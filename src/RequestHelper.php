<?php
namespace linkprofit\trackerApiClient;

class RequestHelper
{
    /**
     * Массив параметров объектов для получения данных от api
     * @var array
     *
     * Ключ - название объекта. Массив настроек объекта должен содержать:
     * url - путь к объекту api, required - список обязательных полей, filter - список доступных фильтров
     */
    public static $settingsForGet = [
        'userAuth' => [
            'url' => '/authorization/user',
            'required' => ['userName','userPassword'],
            'filter' => [],
        ],
        'administratorAuth' => [
            'url' => '/authorization/employer',
            'required' => ['userName','userPassword'],
            'filter' => [],
        ],
        'users' => [
            'url' => '/administration/read/users/list',
            'required' => [],
            'filter' => [],
        ],
        'offer' => [
            'url' => '/cabinet/user/read/offer',
            'required' => ['offerId'],
            'filter' => [],
        ],
        'offers' => [
            'url' => '/cabinet/user/read/offers',
            'required' => [],
            'filter' => ['merchantManagerId','categoryId','mainFilterItem','dateInsertedFrom','dateInsertedTo','active','types','fields','offset','limit','orderByField','orderByMethod'],
        ],
        'categories' => [
            'url' => '/cabinet/user/read/all/categories',
            'required' => [],
            'filter' => [],
        ],
        'banners' => [
            'url' => '/cabinet/banners/read/list',
            'required' => [],
            'filter' => ['fields','types','offerId','hidden','active','width','height','mainFilterItem','dateInsertedFrom','dateInsertedTo','offset','limit','orderByField','orderByMethod',],
        ],
        'countries' => [
            'url' => '/cabinet/countries/read/list',
            'required' => [],
            'filter' => [],
        ],
        'traffic' => [
            'url' => '/cabinet/read/traffic-types',
            'required' => [],
            'filter' => [],
        ]
    ];

    /**
     * Позволяет получить настройки для объекта, при помощи которых осуществляется запрос к api
     * Позволяет получить такие данные как url, обязательные и необязательные поля
     * @param $object string Название объекта
     * @param $key string Название настройки
     * @param string $type Тип запроса (get, update, delete)
     * @return array|string
     */
    public static function getObjectSettings($object, $key, $type = 'get')
    {
        switch ($type) {
            case 'get':
                $params = self::$settingsForGet; break;
            default:
                $params = self::$settingsForGet;
        }

        if (isset($params[$object][$key])) {
            return $params[$object][$key];
        } else {
            return $type == 'url' ? '' : [];
        }
    }
}