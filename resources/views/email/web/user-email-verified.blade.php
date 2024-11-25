
<div class="container text-center">
    <div class="col-lg-5 col-md-4 col-sm-12 col-lg-offset-1 col-md-offset-1">
        <div class="new-logwrap">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
                <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                <h3 class="thank-text text-center">Thank You !!</h3>
                <p class="paragraph-text">You have successfully verified your email.</p>
            </svg>
        </div>
    </div>
</div>

<style>
    .new-logwrap {
        margin: 40px auto 0 auto !important;
        box-shadow: rgb(50 50 93 / 25%) 0px 2px 5px -1px, rgb(0 0 0 / 30%) 0px 1px 3px -1px;
    }

    .thank-text {
        margin-top: 50px;
        font-size: 50px;
        font-weight: 600;
        color: #5A6772 !important;
    }

    .messg-text {
        font-size: 22px;
        margin: 35px 0;
        text-align: center;
    }

    .paragraph-text {
        font-size: 22px;
        margin: 35px 0;
        text-align: center;
    }

    .anchor-text {
        color: blue;
    }

    .btn.btn-popular-jobs {
        padding: 17px;
        display: inline-block;
        width: auto !important;
        font-size: 16px;
        font-weight: 500;
        border-radius: 0;
        background: #2cc4b4 !important;
        color: #fff;
    }

    .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 2;
        stroke-miterlimit: 10;
        stroke: #7ac142;
        fill: none;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark {
        width: 56px !important;
        height: 56px !important;
        border-radius: 50%;
        display: block;
        stroke-width: 2;
        stroke: #fff;
        stroke-miterlimit: 10;
        margin: 4% auto;
        box-shadow: inset 0px 0px 0px #7ac142;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        position: relative !important;
    }

    .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    .text-center {
        text-align: center;
    }

    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes scale {

        0%,
        100% {
            transform: none;
        }

        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }

    @keyframes fill {
        100% {
            box-shadow: inset 0px 0px 0px 30px #7ac142;
        }
    }
</style>
