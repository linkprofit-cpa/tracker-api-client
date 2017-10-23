<?php

namespace linkprofit\trackerApiClient\builder;

class OffersBuilder extends TrackerBuilder
{
    public $count;
    protected $entity = 'offers';
    protected $offers = [];

    /**
     * @param $id
     * @return $this
     */
    public function categoryId($id)
    {
        $this->params['categoryId'] = $id;

        return $this;
    }

    /**
     * @return $this
     */
    public function isActive()
    {
        $this->params['active'] = 1;

        return $this;
    }

    /**
     * @return $this
     */
    public function notHidden()
    {
        $this->params['hidden'] = 0;

        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->params['limit'] = $limit;

        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->params['offset'] = $offset;

        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function orderByField($field)
    {
        $this->params['orderByField'] = $field;

        return $this;
    }

    /**
     * @return array
     */
    protected function handle()
    {
        $this->count = count($this->data['data']);

        array_map(array($this, 'addOffer'), $this->data['data']);

        return $this->offers;
    }

    /**
     * @param $data
     */
    protected function addOffer($data)
    {
        $this->offers[] = $this->handleOffer($data);
    }

    protected function handleOffer($data)
    {
        return [$data['offerId'], $data['name']];
    }
}