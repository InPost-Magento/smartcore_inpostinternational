<?php

namespace Smartcore\InPostInternational\Ui\Component\Form\Element\Address;

use Smartcore\InPostInternational\Ui\Component\Form\Element\AbstractInput;

class Postcode extends AbstractInput
{

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getData('config');
        $data= $this->request->getParams();

        if (isset($config['dataScope']) && $config['dataScope'] == 'post_code') {
            $config['default'] = $this->order->getShippingAddress()->getPostcode();
            if (isset($data['post_code'])) {
                $config['default'] = $data['post_code'];
            }
            $this->setData('config', (array)$config);
        }
    }
}
