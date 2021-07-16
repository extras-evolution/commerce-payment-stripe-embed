if(stripeConfig['intent_key']){
// A reference to Stripe.js initialized with your real test publishable API key.
var stripe = Stripe(stripeConfig['public_key']);
// The items the customer wants to buy
var purchase = {
    items: [{ id: "xl-tshirt" }]
};
// Disable the button until we have Stripe set up on the page
document.querySelector("button").disabled = true;

var elements = stripe.elements({
    locale: 'en'
});
var style = {
    base: {
        color: "#32325d",
        fontFamily: 'Arial, sans-serif',
        fontSmoothing: "antialiased",
        fontSize: "16px",
        "::placeholder": {
            color: "#32325d"
        }
    },
    invalid: {
        fontFamily: 'Arial, sans-serif',
        color: "#fa755a",
        iconColor: "#fa755a"
    }
};
var card = elements.create("card", { style: style });
// Stripe injects an iframe into the DOM
card.mount("#card-element");
card.on("change", function (event) {
    // Disable the Pay button if there are no card details in the Element
    document.querySelector("button").disabled = event.empty;
    document.querySelector("#card-error").textContent = event.error ? event.error.message : "";
});
var form = document.getElementById("stripe-form");
form.addEventListener("submit", function(event) {
    event.preventDefault();
    // Complete payment when the submit button is clicked
    payWithCard(stripe, card, stripeConfig['intent_key']);
});

// Calls stripe.confirmCardPayment
// If the card requires authentication Stripe shows a pop-up modal to
// prompt the user to enter authentication details without leaving your page.
var payWithCard = function(stripe, card, clientSecret) {
    loading(true);
    stripe
        .confirmCardPayment(clientSecret, {
            payment_method: {
                card: card
            }
        })
        .then(function(result) {
            if (result.error) {
                // Show error to your customer
                showError(result.error.message);
            } else {
                // The payment succeeded!
                orderComplete(result.paymentIntent.id);
            }
        });
};
/* ------- UI helpers ------- */
// Shows a success message when the payment is complete
var orderComplete = function(paymentIntentId) {
    loading(false);

    console.log('orderComplete')
    document.querySelector("#stripe-payment-process").classList.remove("hidden");
    document.querySelector("button").disabled = true;

    var req = 0;
    var interval = setInterval(function () {

        console.log('interval '+ req);


        $.get('stripe-embed-check-success',{
            payment_hash:stripeConfig['payment_hash']
        },function (resp) {
            if(resp['status']){
                location.href = resp['redirect'];
            }
        })

        if(req>20){
            clearInterval(interval);
            location.href = stripeConfig['reserve_page_url'];
        }
        req++;
    },3000);
};
// Show the customer the error from Stripe if their card fails to charge
var showError = function(errorMsgText) {
    console.log(errorMsgText)
    loading(false);
    var errorMsg = document.querySelector("#card-error");
    errorMsg.textContent = errorMsgText;
    setTimeout(function() {
        errorMsg.textContent = "";
    }, 4000);
};
// Show a spinner on payment submission
var loading = function(isLoading) {
    if (isLoading) {
        // Disable the button and show a spinner
        document.querySelector("button").disabled = true;
        document.querySelector("#stripe-spinner").classList.remove("hidden");
        document.querySelector("#button-text").classList.add("hidden");
    } else {
        document.querySelector("button").disabled = false;
        document.querySelector("#stripe-spinner").classList.add("hidden");
        document.querySelector("#button-text").classList.remove("hidden");
    }
};
}