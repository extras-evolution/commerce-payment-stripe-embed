# install

1. Create webhook on event checkout.session.completed to url /commerce/stripe-embed/payment-process/
2. Create page where will be payment form, and add [!CommercePaymentStripeEmbedForm!] in page content
3. Create reserve page, where user will be redirected if we don't receive webhook in time.
2. Set Public key,Secret key, reserve page id, paid page id.
3. Install requires

    * stripe/stripe-php

## Snippet param

1. formTpl - payment form template


