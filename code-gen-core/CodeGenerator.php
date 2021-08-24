<?php

namespace CodeGen;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;

class CodeGenerator extends Action
{
    private $attributesDefinition;
    private $constructor;
    private $jsonSerialize;
    private $gettersAndSetters;
    private $paramsToConstructor;
    private $docParamsToConstructor;
    private $setAttributesToConstructor;
    private $getAttributesToJsonSerialize;

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $data = $this->getFormData();

        if ((!isset($data->definitions) && empty($data->definitions)) || (!isset($data->paths) && empty($data->paths))) {
            var_dump('Estrutura Incorreta');
            die;
        }

        foreach ($data->definitions as $key => $value) {
            $this->processDefinition($key, $value);
        }

        foreach ($data->paths as $path) {
            $this->processPath($path);
        }

        return $this->respondWithData(null);
    }

    private function processPath($path)
    {
        var_dump($path);
        die;
    }

    private function processDefinition($definitionKey, $definitionValue): void
    {
        if (!stripos($definitionKey, 'Validator')) {
            $attributesAndTypes = $this->processDefinitionValueToAttributesDefinition($definitionValue);
            $this->createDomainClass($definitionKey, $attributesAndTypes);
        }
    }

    private function processDefinitionValueToAttributesDefinition($definitionValue): array
    {
        if (!isset($definitionValue->properties) && empty($definitionValue->properties)) {
            var_dump('Estrutura Incorreta');
            die;
        }

        $attributesAndTypes = [];
        foreach ($definitionValue->properties as $key => $property) {
            $attributesAndTypes[$key] = $property->type;
        }
        return $attributesAndTypes;
    }

    private function createDomainClass($className, $attributesAndTypes): void
    {
        $this->makeDomainContent($attributesAndTypes);

        $fileDir = __DIR__ . "\..\src\Domain\\$className";
        if (!file_exists($fileDir)) {
            mkdir($fileDir);
        }
        $file = fopen($fileDir . "\\$className.php", 'w');
        fwrite($file, "<?php\ndeclare(strict_types=1);\n\nnamespace App\Domain\\$className;\n\nuse JsonSerializable;\n\nclass $className implements JsonSerializable {\n$this->attributesDefinition\n\n\t$this->constructor\n\n$this->gettersAndSetters\n\n\t$this->jsonSerialize\n}");
        fclose($file);
    }

    private function makeDomainContent($attributesAndTypes): void
    {
        foreach ($attributesAndTypes as $attribute => $type) {
            $this->makeAttributeDefinition($type, $attribute);
            $this->makeDocParamsToConstructor($type, $attribute);
            $this->makeParamsToConstructor($type, $attribute);
            $this->makeGetterrsAndSetters($type, $attribute);
            $this->makeSetAttributesToConstructor($attribute);
            $this->makeGetAttributesToJsonSerialize($attribute);
        }
        $this->makeConstructor();
        $this->makeJsonSerialize();
    }

    private function makeAttributeDefinition($type, $attribute): void
    {
        if (!empty($this->attributesDefinition)) {
            $this->attributesDefinition = $this->attributesDefinition . "\n\n";
        }
        $this->attributesDefinition = $this->attributesDefinition . "\t/**\n\t * @var $type\n\t */\n\tprivate $type $$attribute;";
    }

    private function makeConstructor(): void
    {
        $this->constructor = "/**\n$this->docParamsToConstructor\n\t */\n\tpublic function __construct($this->paramsToConstructor)\n\t{\n$this->setAttributesToConstructor\n\t}";
    }

    private function makeJsonSerialize(): void
    {

        $this->jsonSerialize = "/**\n\t * @return array\n\t */\n\tpublic function jsonSerialize()\n\t{\n\t\treturn [$this->getAttributesToJsonSerialize\n\t\t];\n\t}";
    }

    private function makeGetterrsAndSetters($type, $attribute): void
    {
        if (!empty($this->gettersAndSetters)) {
            $this->gettersAndSetters = $this->gettersAndSetters . "\n\n";
        }

        $functionName = str_replace('_', '', ucwords($attribute, '_'));

        $this->gettersAndSetters = $this->gettersAndSetters . "\t/**\n\t * @return $type\n\t */\n\tpublic function get$functionName(): $type\n\t{\n\t\treturn \$this->$attribute;\n\t}\n\n\t/**\n\t * @param $type $$attribute\n\t * @void\n\t */\n\tpublic function set$functionName($type $$attribute): void\n\t{\n\t\t\$this->$attribute = $$attribute;\n\t}";
    }

    private function makeDocParamsToConstructor($type, $attribute): void
    {
        if (!empty($this->docParamsToConstructor)) {
            $this->docParamsToConstructor = $this->docParamsToConstructor . "\n";
        }
        $this->docParamsToConstructor = $this->docParamsToConstructor . "\t * @param $type $$attribute";
    }

    private function makeParamsToConstructor($type, $attribute): void
    {
        if (!empty($this->paramsToConstructor)) {
            $this->paramsToConstructor = $this->paramsToConstructor . ", ";
        }
        $this->paramsToConstructor = $this->paramsToConstructor . "$type $$attribute";
    }

    private function makeSetAttributesToConstructor($attribute): void
    {
        if (!empty($this->setAttributesToConstructor)) {
            $this->setAttributesToConstructor = $this->setAttributesToConstructor . "\n";
        }
        $this->setAttributesToConstructor = $this->setAttributesToConstructor . "\t\t\$this->$attribute = $$attribute;";
    }

    private function makeGetAttributesToJsonSerialize($attribute): void
    {
        if (!empty($this->setAttributesToConstructor)) {
            $this->getAttributesToJsonSerialize = $this->getAttributesToJsonSerialize . "\n";
        }
        $this->getAttributesToJsonSerialize = $this->getAttributesToJsonSerialize . "\t\t\t'$attribute' => \$this->$attribute,";
    }
}