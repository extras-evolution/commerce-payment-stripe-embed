<?php

if (empty($modx->commerce) && !defined('COMMERCE_INITIALIZED')) {
    return;
}
/** @var \Commerce\Commerce $commerce */
$commerce = ci()->commerce;
$orderProcessor = $commerce->loadProcessor();

$lang = $commerce->getUserLanguage('stripe-embed');

$isSelectedPayment = !empty($order['fields']['payment_method']) && $order['fields']['payment_method'] == 'stripe-embed';


switch ($modx->event->name) {
    case 'OnRegisterPayments':


        $class = new \Commerce\Payments\StripeEmbedPayment($modx, $params);
        if (empty($params['title'])) {
            $params['title'] = $lang['stripe-embed.caption'];
        }


        $commerce->registerPayment('stripe-embed', $params['title'], $class);
        break;


    case 'OnBeforeOrderSending':
        if ($isSelectedPayment) {
            $FL->setPlaceholder('extra', $FL->getPlaceholder('extra', '') . $commerce->loadProcessor()->populateOrderPaymentLink());
        }

        break;


    case 'OnPageNotFound': {
        $q = trim($_GET['q'],'/');

        switch ($q){
            case 'stripe-embed-check-success':

                $payment = $orderProcessor->loadPaymentByHash($_GET['payment_hash']);


                if($payment['paid']){
                    $resp = [
                        'status'=>true,
                        'redirect'=> '/commerce/stripe-embed/payment-success?payment_hash='.$payment['hash']
                    ];
                }
                else{
                    $resp = [
                        'status'=>false,
                    ];
                }

                header('Content-type: text/json');
                echo json_encode($resp);

                die();
                break;
        }

        break;
    }
    case 'OnManagerBeforeOrderRender': {
        if (isset($params['groups']['payment_delivery']) && $isSelectedPayment) {
            $params['groups']['payment_delivery']['fields']['payment_link'] = [
                'title'   => $lang['stripe-embed.link_caption'],
                'content' => function($data) use ($commerce) {
                    return $commerce->loadProcessor()->populateOrderPaymentLink('@CODE:<a href="[+link+]" target="_blank">[+link+]</a>');
                },
                'sort' => 50,
            ];
        }
        break;
    }
}