<?php

namespace DM\LighthouseSchemaGenerator\Parsers;

use Exception;
use ReflectionMethod;
use Illuminate\Database\Eloquent\Model;
use DM\LighthouseSchemaGenerator\Helpers\Reflection;

class ModelParser
{
    /** @var Reflection  */
    protected $reflection;

    /** @var ColumnsParser */
    private $columnsParser;

    /** @var MethodParser */
    private $methodParser;

    public function __construct(Reflection $reflection, MethodParser $methodParser, ColumnsParser $columnsParser)
    {
        $this->reflection = $reflection;
        $this->methodParser = $methodParser;
        $this->columnsParser = $columnsParser;
    }

    /**
     * @param Model $model
     * @return string $data
     */
    public function parse(Model $model): string
    {
        $data = '';
        $reflector = $this->reflection->reflectionObject($model);

        $data .= "type {$reflector->getShortName()} {\n";
        $data .= $this->columnsParser->parse($model);

        $publicMethods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($publicMethods as $reflectionMethod) {
            try {
                $data .= $this->methodParser->parse($model, $reflectionMethod);
            } catch (Exception $exception) {
                continue;
            }
        }

        $data .= '}';

        return $data;
    }
}