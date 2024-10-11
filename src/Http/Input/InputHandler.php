<?php

namespace Demo\Http\Input;

use Demo\Http\Input\Interface\IInputItem;
use Demo\Http\Request;

class InputHandler
{

    /**
     * @var array
     */
    protected array $get = [];

    /**
     * @var array
     */
    protected array $post = [];


    /**
     * Original get/params variables
     * @var array
     */
    protected array $originalParams = [];


    /**
     * @var Request
     */
    protected Request $request;

    /**
     * Original post variables
     * @var array
     */
    protected array $originalPost = [];

    /**
     * Input constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->parseInputs();
    }

    /**
     * Parse input values
     *
     */
    public function parseInputs(): void
    {
        /* Parse get requests */
        if (count($_GET) !== 0) {
            $this->originalParams = $_GET;
            $this->get = $this->parseInputItem($this->originalParams);
        }

        /* Parse post requests */
        $this->originalPost = $_POST;

        if ($this->request->isPostBack() === true) {

            $contents = file_get_contents('php://input');

            // Append any PHP-input json
            if (strpos(trim($contents), '{') === 0) {
                $post = json_decode($contents, true);

                if ($post !== false) {
                    $this->originalPost += $post;
                }
            } else {
                $post = [];
                parse_str($contents, $post);
                $this->originalPost += $post;
            }
        }

        if (count($this->originalPost) !== 0) {
            $this->post = $this->parseInputItem($this->originalPost);
        }

    }

    /**
     * Parse input item from array
     *
     * @param array $array
     * @return array
     */
    protected function parseInputItem(array $array): array
    {
        $list = [];

        foreach ($array as $key => $value) {

            // Handle array input
            if (is_array($value) === true) {
                $value = $this->parseInputItem($value);
            }

            $list[$key] = new InputItem($key, $value);
        }

        return $list;
    }

    /**
     * Get input element value matching index
     *
     * @param string $index
     * @param string|mixed|null $defaultValue
     * @param array ...$methods
     * @return string|array
     */
    public function value(string $index, $defaultValue = null, ...$methods)
    {
        $input = $this->find($index, ...$methods);

        if ($input instanceof IInputItem) {
            $input = $input->getValue();
        }

        /* Handle collection */
        if (is_array($input) === true) {
            $output = $this->getValueFromArray($input);

            return (count($output) === 0) ? $defaultValue : $output;
        }

        return ($input === null || (is_string($input) && trim($input) === '')) ? $defaultValue : $input;
    }

    protected function getValueFromArray(array $array): array
    {
        $output = [];
        /* @var $item InputItem */
        foreach ($array as $key => $item) {

            if ($item instanceof IInputItem) {
                $item = $item->getValue();
            }

            $output[$key] = is_array($item) ? $this->getValueFromArray($item) : $item;
        }

        return $output;
    }

    /**
     * Find input object
     *
     * @param string $index
     * @param array ...$methods
     * @return IInputItem|array|null
     */
    public function find(string $index, ...$methods)
    {
        $element = null;

        if (count($methods) > 0) {
            $methods = is_array(...$methods) ? array_values(...$methods) : $methods;
        }

        if (count($methods) === 0 || in_array(Request::REQUEST_TYPE_GET, $methods, true) === true) {
            $element = $this->get($index);
        }

        if (($element === null && count($methods) === 0) || (count($methods) !== 0 && in_array(Request::REQUEST_TYPE_POST, $methods, true) === true)) {
            $element = $this->post($index);
        }


        return $element;
    }

    /**
     * Find post-value by index or return default value.
     *
     * @param string $index
     * @param mixed|null $defaultValue
     * @return InputItem|array|string|null
     */
    public function post(string $index, $defaultValue = null)
    {
        return $this->post[$index] ?? $defaultValue;
    }

    /**
     * Find parameter/query-string by index or return default value.
     *
     * @param string $index
     * @param mixed|null $defaultValue
     * @return InputItem|array|string|null
     */
    public function get(string $index, $defaultValue = null)
    {
        return $this->get[$index] ?? $defaultValue;
    }

    /**
     * Get all get/post items
     * @param array $filter Only take items in filter
     * @return array
     */
    public function all(array $filter = []): array
    {
        $output = $this->originalParams + $this->originalPost;
        $output = (count($filter) > 0) ? array_intersect_key($output, array_flip($filter)) : $output;

        foreach ($filter as $filterKey) {
            if (array_key_exists($filterKey, $output) === false) {
                $output[$filterKey] = null;
            }
        }

        return $output;
    }

}