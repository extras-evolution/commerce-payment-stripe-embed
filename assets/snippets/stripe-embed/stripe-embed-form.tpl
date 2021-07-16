<div class="stripe-embed">
    <script>
        var stripeConfig = [+config+];
    </script>

    <form id="stripe-form">
        [[if? &is=`[+errors+]:!empty` &then=`
        <p class="card-error" role="alert">[+errors+]</p>
        ` &else=`
        <div id="card-element"><!--Stripe.js injects the Card Element--></div>
        <button id="submit">
            <div class="spinner hidden" id="stripe-spinner"></div>
            <span id="button-text">[+lang.stripe-embed.pay-now+]</span>
        </button>

        <p class="card-error" id="card-error" role="alert"></p>
        <div class="payment-process hidden" id="stripe-payment-process">
            <span>[+lang.stripe-embed.payment-process+]</span>
            <div class="spinner spinner-blue" ></div>
        </div>
        `]]

    </form>
</div>