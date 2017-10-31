<?php
namespace linkprofit\trackerApiClient;
use Yii;
use yii\base\Object;
use yii\base\InvalidParamException;
use yii\helpers\Json;
use linkprofit\trackerApiClient\exceptions\RequestException;
use linkprofit\trackerApiClient\exceptions\ResponseException;

/**
 * Данный класс используется для получения данных от api
 *
 * Для осуществления запроса использовуйте метод `get`
 * В данный метод нужно передать $object - строку указывающую на объект, который будет получен из трекера
 * Вторым параметром нужно передать $params - массив параметров запроса (обязательных и необязательных).
 * Конфигурация объектов находится в классе ApiConfig
 */
class Request extends Object
{
    /**
     * @var Connection
     */
    public $connection;

    protected $object;
    protected $method = 'PUT';

    protected $requiredParams = [];
    protected $filterParams = [];

    protected $requestHttpCode;

    protected $apiCallCount;

    protected $dualRoutes = [
        'offers', 'banners', 'offer', 'banner'
    ];

    /**
     * Метод используется для получения данных из трекера
     * @param $object string Название объекта, для которого будет осуществлен запрос к трекеру
     *
     * - auth - Auth token
     * - offer - Единичный оффер
     * - offers - Список офферов
     * - categories - Список категорий
     * - categories - Список баннеров
     *
     * @param $params array Параметры для запроса. Ниже приведен список обязательных параметров и возможных фильтров для запроса
     *
     * - auth - Обязательные: userName, userPassword; Фильтры: нет;
     * - offer - Обязательные: offerId; Фильтры: нет;
     * - offers - Обязательные: нет; Фильтры: merchantManagerId, categoryId, mainFilterItem, dateInsertedFrom, dateInsertedTo, active, types, fields, offset, limit, orderByField, orderByMethod
     * - categories - Обязательные: нет; Фильтры: нет;
     * - banners - Обязательные: нет; Фильтры: fields, types, offerId, hidden, active, width, height, mainFilterItem, dateInsertedFrom, dateInsertedTo, offset, limit, orderByField, orderByMethod;
     *
     * @param $iteration int
     * @throws RequestException
     * @throws ResponseException
     * @return string ApiResponse
     */
    public function get($object, $params = [], $iteration = 0)
    {
        if (in_array($object, $this->dualRoutes)) {
            $status = ($this->connection->isAdmin === false) ? 'user' : 'administrator';
            $object = $status . ucfirst($object);
        }
        $this->object = $object;
        $this->requiredParams = RequestHelper::getObjectSettings($object, 'required');
        $this->filterParams = RequestHelper::getObjectSettings($object, 'filter');
        $url = RequestHelper::getObjectSettings($object, 'url');

        if (empty($object) || !is_string($object))
            throw new RequestException('Request object must be string');
        if (!$this->checkRequired($params))
            throw new RequestException('These params are required for this call: '.implode(', ', $this->requiredParams));
        if (empty($url))
            throw new RequestException("Can't find url for this object: {$object}");

        $authToken = in_array($object, ['userAuth', 'administratorAuth']) ? null : $this->connection->getAuthToken();

        $response = $this->queryApi($url, $authToken, $this->prepareParams($params), $this->method);

        try {
            $this->parseResponse($response, $url, $params);
        } catch (ResponseException $e) {
            if ($e->getCode() == 111 || $e->getCode() == 110) {
                $this->connection->connect($iteration);
                $response = $this->get($object, $params, ++$iteration);
            } else {
                throw $e;
            }
        }

        return $response;
    }

    /**
     * Проверяет наличие обязательных параметров в запросе
     * @param $fields
     * @return bool
     */
    protected function checkRequired($fields)
    {
        foreach ($this->requiredParams as $param) {
            if (!array_key_exists($param, $fields))
                return false;
        }

        return true;
    }

    /**
     * Подготавливает параметры для отправки, отсеивает параметры, не использующиеся в api
     * @param $params
     * @return array
     */
    protected function prepareParams($params)
    {
        $fields = array_merge($this->requiredParams, $this->filterParams);

        return array_intersect_key($params, array_flip($fields));
    }

    /**
     * Осуществляет запрос к api
     * @param $url
     * @param null $authToken
     * @param array $params
     * @param string $method
     * @return mixed|string
     */
    protected function queryApi($url, $authToken = null, $params = [], $method = 'PUT')
    {
        if ($authToken !== null) $params['authToken'] = $authToken;

        $ch = curl_init($this->connection->getApiUrl().$url);

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_VERBOSE => true,
        ];

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        $this->requestHttpCode = curl_errno($ch) == 28 ? 'timeout' : curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return !$response ? '' : $response;
    }

    /**
     * Обрабатывает ответ api и возвращает ответ в виде массива
     * @param $response
     * @param $route
     * @param $params
     * @return mixed
     * @throws ResponseException
     */
    protected function parseResponse($response, $route, $params)
    {
        switch ($this->requestHttpCode) {
            case 'timeout':  throw new ResponseException('Таймаут соединения с сервером.', 408, $response, $route, $params); break;
            case 404:        throw new ResponseException('Страница не найдена.', 404, $response, $route, $params); break;
            case 500:        throw new ResponseException('Непредвиденная ошибка сервера.', 500, $response, $route, $params); break;
        }

        try {
            $decodedResponse = Json::decode($response);
        } catch (InvalidParamException $e) {
            throw new ResponseException( 'Некорректный ответ сервиса.', 000, $response, $route, $params );
        }

        if( isset($decodedResponse['success']) and $decodedResponse['success'] == false ) {
            switch($decodedResponse['code']) {
                case 101 : throw new ResponseException( 'Пользователь с таким логином и паролем не найден в системе', 101, $response, $route, $params ); break;
                case 107 : throw new ResponseException( 'Логин сотрудника не был передан в систему для аутентификации', 107, $response, $route, $params ); break;
                case 108 : throw new ResponseException( 'Пароль сотрудника не был передан в систему для аутентификации', 108, $response, $route, $params ); break;
                case 109 : throw new ResponseException( 'Сотрудник с таким логином и паролем не найден в системе', 109, $response, $route, $params ); break;
                case 110 : throw new ResponseException( 'Пользователь не авторизован. Необходима авторизация (истек срок годности auth token)', 110, $response, $route, $params); break;
                case 111 : throw new ResponseException( 'Сотрудник не авторизован, требуется повторная авторизация', 111, $response, $route, $params); break;
                case 112 : throw new ResponseException( 'Сотрудник не имеет доступа к данной операции', 112, $response, $route, $params); break;
                case 113 : throw new ResponseException( 'Пароль и его подтверждение не совпадают', 113, $response, $route, $params); break;
                case 114 : throw new ResponseException( 'Что то из необходимых параметров не передано', 114, $response, $route, $params); break;
                case 116 : throw new ResponseException( 'Такой пользователь уже существует в системе', 116, $response, $route, $params); break;
                case 117 : throw new ResponseException( 'Оффер с таким именем уже существует', 117, $response, $route, $params); break;
                case 118 : throw new ResponseException( 'Комиссия оффера с таким именем для этого оффера уже существует', 118, $response, $route, $params); break;
                case 121 : throw new ResponseException( 'Баннер уже существует в системе', 121, $response, $route, $params); break;
                case 127 : throw new ResponseException( 'Сайт с таким доменным именем уже закреплен за вебмастером', 127, $response, $route, $params); break;
                case 128 : throw new ResponseException( 'Один и тот же тип траффика добавлен в одобренные и запрещенные типы траффика', 128, $response, $route, $params); break;
                case 130 : throw new ResponseException( 'Данные кошельков повторяются', 130, $response, $route, $params); break;
                case 143 : throw new ResponseException( 'Дубликат алиаса в системе', 143, $response, $route, $params); break;
                case 141 :
                    $refId = [];
                    preg_match('/#\d+/ui', $decodedResponse['message'], $refId);
                    $message = (!empty($refId[0])) ? "Вебмастер с refId {$refId[0]} не найден в системе" : "Некорректный ответ сервера";
                    throw new ResponseException($message, 141, $response, $route, $params);
                    break;
                default : throw new ResponseException( 'Необработанная или неизвестная ошибка', 666, $response, $route, $params);
            }
        }

        return $decodedResponse;
    }
}