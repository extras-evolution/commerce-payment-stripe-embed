<?php

namespace Commerce\Payments;

use Commerce\Commerce;

class StripeEmbedPayment extends Payment
{

    /**
     * @var $commerce Commerce
     */
    private $commerce;
    /**
     * @var \Commerce\Processors\OrdersProcessor
     */
    private $orderProcessor;

    public function __construct($modx, array $params = [])
    {
        parent::__construct($modx, $params);

        $this->commerce = ci()->commerce;
        $this->orderProcessor = $this->commerce->loadProcessor();
        $this->lang = $this->commerce->getUserLanguage('authorize');

    }

    public function getPaymentLink()
    {

        $processor = $this->commerce->loadProcessor();
        $order = $processor->getOrder();



        $payment = $this->createPayment($order['id'], $order['amount']);
        $this->orderProcessor->savePayment($payment);


        return $this->modx->makeUrl($this->getSetting('payment_page_id')) . '?' . http_build_query([
                    'payment_hash' => $payment['hash'],
                ]
            );
    }

    public function handleCallback()
    {
        $payload = @file_get_contents('php://input');
        $this->log($payload);

        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
            );
        } catch(\UnexpectedValueException $e) {
            $this->log('Webhook error while parsing basic request',3);
            http_response_code(400);
            exit();
        }


        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':

                $object = $event->data->object;

                try {
                    $this->orderProcessor->processPayment($object->metadata->payment_id, floatval($object->amount_received) * 0.01);
                    $this->log('Payment process success');
                    return true;
                } catch (\Exception $e) {
                    $this->log('processPaymentError, exception message - '.$e->getMessage(),3);
                    http_response_code(400);
                    exit();
                }
                break;
        }

        http_response_code(200);
        return true;
    }

    private function log($error,$level = 1)
    {
        if($this->getSetting('debug') == 0 && $level<2){
          return;
        }

        $this->modx->logEvent(150,$level,$error,'stripe-embed');
    }

    public function getRequestPaymentHash()
    {
        return $_GET['payment_hash'];
    }



    public function handleSuccess()
    {
        $payment = $this->orderProcessor->loadPaymentByHash($this->getRequestPaymentHash());

        return $payment['paid'] == 1;
    }

    public function handleError()
    {
        return true;
    }

}