<?php
$render = DLTemplate::getInstance($modx);
$formTpl = isset($formTpl) ? $formTpl : '@CODE:' . @file_get_contents(MODX_BASE_PATH . 'assets/snippets/stripe-embed/stripe-embed-form.tpl');
$settings = json_decode($modx->pluginCache['CommercePaymentStripeEmbedProps'], true);
$reservePageUrl = $modx->makeUrl($settings['reserve_page_id']);

\Stripe\Stripe::setApiKey($settings['secret_key']);
/** @var \Commerce\Commerce $commerce */
$commerce = $modx->commerce;
$orderProcessor = $commerce->loadProcessor();
$payment = $orderProcessor->loadPaymentByHash($_GET['payment_hash']);


$lexicon = new Helpers\Lexicon($modx);

$locations = [
    'english' => 'en',
    'russian-UTF8' => 'ru',
    'ukrainian' => 'uk'
];

$locale = isset($locale[$commerce->getCurrentLang()]) ? $locale[$commerce->getCurrentLang()] : 'en';


foreach ($commerce->langRelated as $key => $lang) {
    if ($commerce->getCurrentLang() == $lang) {
        $locale = $key;
        break;
    }
}


$lang = $commerce->getUserLanguage('stripe-embed');
$order = $orderProcessor->loadOrder($payment['order_id']);

if (empty($payment)) {
    $modx->sendRedirect($modx->makeUrl($commerce->getSetting('payment_failed_page_id')));
}
$errors = [];
$paymentIntentKey = '';
if ($payment['paid'] != 0) {
    $errors[] = $lang['stripe-embed.payment-already-paid'];
}


$modx->regClientCSS('assets/snippets/stripe-embed/style.css');
$modx->regClientScript('https://js.stripe.com/v3/');
$modx->regClientScript('https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch');
$modx->regClientScript('assets/snippets/stripe-embed/script.js');

if (empty($errors)) {

    try {
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => intval($payment['amount']),
            'currency' => $order['currency'],
            'metadata' => [
                'payment_id' => $payment['id']
            ]
        ]);
        $paymentIntentKey = $paymentIntent->client_secret;

    } catch (Exception $e) {
        $errors[] = $e->getMessage();

    }
}

echo $render->parseChunk($formTpl, [
    'errors' => implode(',', $errors),
    'lang' => $lang,
    'config' => json_encode([
        'public_key' => $settings['public_key'],
        'intent_key' => $paymentIntentKey,
        'reserve_page_url' => $reservePageUrl,
        'payment_hash' => $payment['hash'],
        'locale' => $locale
    ], JSON_PRETTY_PRINT)

]);
return;