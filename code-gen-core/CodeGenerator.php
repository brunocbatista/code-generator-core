<?php

namespace CodeGen;

use App\Application\Actions\Action;
use App\Application\Exceptions\InternalServerException;
use Psr\Http\Message\ResponseInterface as Response;

class CodeGenerator extends Action
{
    private $attributesDefinition;
    private $gettersAndSetters;
    private $paramsToConstructor;
    private $docParamsToConstructor;
    private $setAttributesToConstructor;
    private $getAttributesToJsonSerialize;
    private $contentToRouteFile;

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $data = $this->getFormData();

        if ((!isset($data->definitions) && empty($data->definitions)) || (!isset($data->paths) && empty($data->paths))) {
            throw new InternalServerException();
        }

        foreach ($data->definitions as $key => $value) {
            $this->processDefinition($key, $value);
        }

        foreach ($data->paths as $route => $body) {
            $this->processPath($route, $body);
        }

        $this->processRouteFile();

        return $this->respondWithData('Entrada processada com sucesso!');
    }

    private function processRouteFile(): void
    {
        $routeFileDir = __DIR__ . '\..\app\routes.php';
        $routeFileContent = file_get_contents($routeFileDir);
        $routeFileContent = str_replace('// code-gen space', $this->contentToRouteFile, $routeFileContent);
        $file = fopen($routeFileDir, 'w');
        fwrite($file, $routeFileContent);
        fclose($file);
    }

    private function processDefinition($definitionKey, $definitionValue): void
    {
        if (!stripos($definitionKey, 'Validator')) {
            $attributesAndTypes = $this->processDefinitionValueToAttributesDefinition($definitionValue);
            $this->createDomainClass($definitionKey, $attributesAndTypes);
        }
    }

    private function processPath($route, $body)
    {
        switch ($route) {
            case '/login':
                $this->makeRouteContent('post', '/login', '\App\Application\Actions\Auth\\AuthUserAction::class');
                $this->makeBaseAuthActionFile();
                $this->makeAuthActionFile('Login');
                break;
            case '/register':
                $this->makeRouteContent('post', '/register', '\App\Application\Actions\Auth\\RegisterUserAction::class');
                $this->makeBaseAuthActionFile();
                $this->makeAuthActionFile('Register');
                break;
            default:
                $explodedRoutes = explode('/', $route);
                $lastIsIdDetail = $explodedRoutes[count($explodedRoutes) - 1] == '{id}';
                $originalClassName = $this->transformToSigular($lastIsIdDetail ? $explodedRoutes[count($explodedRoutes) - 2] : $explodedRoutes[count($explodedRoutes) - 1]);
                $camelClassName = $this->transformToSigular(ucwords($originalClassName));

                $this->makeBaseActionFile($camelClassName, $originalClassName);

                foreach ($body as $key => $params) {
                    switch ($key){
                        case 'get':
                            if ($lastIsIdDetail) {
                                $method = 'View';
                                $this->makeActionFile($method, $camelClassName, $originalClassName);
                            } else {
                                $method = 'List';
                                $this->makeActionFile($method, $camelClassName, $originalClassName);
                                $this->makeNotFoundExceptionFile($camelClassName, $originalClassName);
                            }
                            break;
                        case 'post':
                            $method = 'Create';
                            $this->makeActionFile($method, $camelClassName, $originalClassName);
                            break;
                        case 'put':
                            $method = 'Update';
                            $this->makeActionFile($method, $camelClassName, $originalClassName);
                            break;
                        case 'delete':
                            $method = 'Delete';
                            $this->makeActionFile($method, $camelClassName, $originalClassName);
                            break;
                    }
                    $this->makeRouteContent($key, $route, "\App\Application\Actions\\$camelClassName\\$method".$camelClassName."Action::class");
                }
        }
    }

    private function makeRouteContent($method, $path, $className): void
    {
        if (!empty($this->contentToRouteFile)) {
            $this->contentToRouteFile = $this->contentToRouteFile . "\n\n\t";
        }
        $modelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\RouteModel.text';
        $modelFileContent = file_get_contents($modelFileDir);
        $modelFileContent = str_replace('//HttpMethod', $method, $modelFileContent);
        $modelFileContent = str_replace('//Route', $path, $modelFileContent);
        $modelFileContent = str_replace('//Class', $className, $modelFileContent);
        $this->contentToRouteFile = $this->contentToRouteFile . $modelFileContent;
    }

    private function processDefinitionValueToAttributesDefinition($definitionValue): array
    {
        if (!isset($definitionValue->properties) && empty($definitionValue->properties)) {
            throw new InternalServerException();
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

        $modelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\DomainModel.text';
        $modelFileContent = file_get_contents($modelFileDir);
        if (empty($modelFileContent)) {
            throw new InternalServerException();
        }
        $modelFileContent = str_replace('//ClassName', $className, $modelFileContent);
        $modelFileContent = str_replace('//AttributesDefinition', $this->attributesDefinition, $modelFileContent);
        $modelFileContent = str_replace('//ParamsDocumentation', $this->docParamsToConstructor, $modelFileContent);
        $modelFileContent = str_replace('//ParamsToConstructor', $this->paramsToConstructor, $modelFileContent);
        $modelFileContent = str_replace('//SetAttributesToConstructor', $this->setAttributesToConstructor, $modelFileContent);
        $modelFileContent = str_replace('//GettersAndSetters', $this->gettersAndSetters, $modelFileContent);
        $modelFileContent = str_replace('//JsonSerializeArray', $this->getAttributesToJsonSerialize, $modelFileContent);


        $fileDir = __DIR__ . "\..\src\Domain\\$className";
        if (!file_exists($fileDir)) {
            mkdir($fileDir);
        }
        $file = fopen($fileDir . "\\$className.php", 'w');
        fwrite($file, $modelFileContent);
        fclose($file);

        $modelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\RepositoryModel.text';
        $modelFileContent = file_get_contents($modelFileDir);
        if (empty($modelFileContent)) {
            throw new InternalServerException();
        }
        $modelFileContent = str_replace('//CamelClassName', $className, $modelFileContent);
        $modelFileContent = str_replace('//OriginalClassName', $className, lcfirst($modelFileContent));

        $fileDir = __DIR__ . "\..\src\Domain\\$className\\";
        if (!file_exists($fileDir)) {
            mkdir($fileDir);
        }
        $file = fopen($fileDir . $className . "Repository.php", 'w');
        fwrite($file, $modelFileContent);
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
    }

    private function makeAttributeDefinition($type, $attribute): void
    {
        if (!empty($this->attributesDefinition)) {
            $this->attributesDefinition = $this->attributesDefinition . "\n\n";
        }
        $modelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\AttributesDefinitionModel.text';
        $modelFileContent = file_get_contents($modelFileDir);
        $modelFileContent = str_replace('//Type', $type, $modelFileContent);
        $modelFileContent = str_replace('//Attribute', $attribute, $modelFileContent);
        $this->attributesDefinition = $this->attributesDefinition . $modelFileContent;
    }

    private function makeGetterrsAndSetters($type, $attribute): void
    {
        if (!empty($this->gettersAndSetters)) {
            $this->gettersAndSetters = $this->gettersAndSetters . "\n\n";
        }

        $functionName = str_replace('_', '', ucwords($attribute, '_'));

        $modelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\GetterAndSetterModel.text';
        $modelFileContent = file_get_contents($modelFileDir);
        $modelFileContent = str_replace('//Type', $type, $modelFileContent);
        $modelFileContent = str_replace('//CamelAttribute', $functionName, $modelFileContent);
        $modelFileContent = str_replace('//OriginalAttribute', $attribute, $modelFileContent);

        $this->gettersAndSetters = $this->gettersAndSetters . $modelFileContent;
    }

    private function makeDocParamsToConstructor($type, $attribute): void
    {
        if (!empty($this->docParamsToConstructor)) {
            $this->docParamsToConstructor = $this->docParamsToConstructor . "\n\t ";
        }
        $this->docParamsToConstructor = $this->docParamsToConstructor . "* @param $type $$attribute";
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
            $this->setAttributesToConstructor = $this->setAttributesToConstructor . "\n\t\t";
        }
        $this->setAttributesToConstructor = $this->setAttributesToConstructor . "\$this->$attribute = $$attribute;";
    }

    private function makeGetAttributesToJsonSerialize($attribute): void
    {
        if (!empty($this->setAttributesToConstructor)) {
            $this->getAttributesToJsonSerialize = $this->getAttributesToJsonSerialize . "\n\t\t\t";
        }
        $this->getAttributesToJsonSerialize = $this->getAttributesToJsonSerialize . "'$attribute' => \$this->$attribute,";
    }

    private function makeBaseActionFile($camelClassName, $originalClassName)
    {
        if (!file_exists(__DIR__ . "\..\src\Application\Actions\\$camelClassName\\" . $camelClassName . 'Action.php')) {
            $modelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\ActionResourceModel.text';
            $modelFileContent = file_get_contents($modelFileDir);
            if (empty($modelFileContent)) {
                var_dump('Estrutura Incorreta');
                die;
            }
            $modelFileContent = str_replace('//CamelClassName', $camelClassName, $modelFileContent);
            $modelFileContent = str_replace('//OriginalClassName', $originalClassName, $modelFileContent);

            $fileDir = __DIR__ . "\..\src\Application\Actions\\$camelClassName\\";
            if (!file_exists($fileDir)) {
                mkdir($fileDir);
            }
            $file = fopen($fileDir . $camelClassName . 'Action.php', 'w');
            fwrite($file, $modelFileContent);
            fclose($file);
        }
    }

    private function makeBaseAuthActionFile()
    {
        if (!file_exists(__DIR__ . '\..\src\Application\Actions\Auth\AuthAction.php')) {
            $modelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\AuthActionResourceModel.text';
            $modelFileContent = file_get_contents($modelFileDir);
            if (empty($modelFileContent)) {
                var_dump('Estrutura Incorreta');
                die;
            }

            $fileDir = __DIR__ . '\..\src\Application\Actions\Auth\\';
            if (!file_exists($fileDir)) {
                mkdir($fileDir);
            }
            $file = fopen($fileDir . 'AuthAction.php', 'w');
            fwrite($file, $modelFileContent);
            fclose($file);
        }
    }

    private function makeActionFile($action, $camelClassName, $originalClassName)
    {
        $modelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\\'.$action.'ResourceModel.text';
        $modelFileContent = file_get_contents($modelFileDir);
        if (empty($modelFileContent)) {
            throw new InternalServerException();
        }
        $modelFileContent = str_replace('//CamelClassName', $camelClassName, $modelFileContent);
        $modelFileContent = str_replace('//OriginalClassName', $originalClassName, $modelFileContent);

        $fileDir = __DIR__ . "\..\src\Application\Actions\\$camelClassName";
        if (!file_exists($fileDir)) {
            mkdir($fileDir);
        }
        $file = fopen($fileDir . "\\$action" . $camelClassName . 'Action.php', 'w');
        fwrite($file, $modelFileContent);
        fclose($file);
    }

    private function makeAuthActionFile($action)
    {
        $modelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\\'.$action.'Model.text';
        $modelFileContent = file_get_contents($modelFileDir);
        if (empty($modelFileContent)) {
            throw new InternalServerException();
        }

        $fileDir = __DIR__ . "\..\src\Application\Actions\Auth";

        if (!file_exists($fileDir)) {
            mkdir($fileDir);
        }
        $actionName = $action === 'Register' ? 'Register' : 'Auth';
        $file = fopen($fileDir . "\\" . $actionName . 'UserAction.php', 'w');
        fwrite($file, $modelFileContent);
        fclose($file);
    }

    private function makeNotFoundExceptionFile($camelClassName, $originalClassName)
    {
        $notFoundNodelFileDir = __DIR__ . '\..\code-gen-core\CodeGenModels\NotFoundExceptionModel.text';
        $notFoundModelFileContent = file_get_contents($notFoundNodelFileDir);
        if (empty($notFoundModelFileContent)) {
            throw new InternalServerException();
        }
        $notFoundModelFileContent = str_replace('//CamelClassName', $camelClassName, $notFoundModelFileContent);
        $notFoundModelFileContent = str_replace('//OriginalClassName', $originalClassName, $notFoundModelFileContent);
        $fileDir = __DIR__ . "\..\src\Application\Exceptions\\$camelClassName/";
        if (!file_exists($fileDir)) {
            mkdir($fileDir);
        }
        $file = fopen($fileDir . $camelClassName . 'NotFoundException.php', 'w');
        fwrite($file, $notFoundModelFileContent);
        fclose($file);
    }

    private function transformToSigular($attribute) {
        return (preg_match('~s$~i', $attribute) > 0) ? rtrim($attribute, 's') : $attribute;
    }
}