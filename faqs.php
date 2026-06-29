<?php include 'header.php'; ?>

<div class="faq-page">

    <section class="faq-hero">
        <span class="faq-label">
            HELP CENTER
        </span>
        <h1>
            Frequently Asked Questions
        </h1>
        <p>
            Everything you need to know about our artisan beans,
            brewing rituals, and how we bring the cafe experience
            to your doorstep.
        </p>
    </section>

    <section class="faq-accordion">
        <div class="faq-item">
    <button class="faq-question">
        Do you process manual GCash payments?
        <span>⌄</span>
    </button>

    <div class="faq-answer">
        Yes. Simply select "Manual GCash Transfer" during checkout,
        transfer to our merchant wallet, and upload your screenshot
        copy for admin validation.
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        Why do you use a Moka Pot?
        <span>⌄</span>
    </button>

    <div class="faq-answer">
        The Moka Pot forces boiling water pressurized by steam through
        coffee grounds. This traditional stovetop method extracts a rich,
        robust, and heavy-bodied coffee that serves as the perfect,
        bold base for our signature drinks.
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        How long does it take to prepare my order?
        <span>⌄</span>
    </button>

    <div class="faq-answer">
        Because we believe in the slow-brew art of the Moka Pot,
        we brew each batch patiently. Please allow around
        10–15 minutes for preparation. You can always monitor
        your order status on your Account Dashboard.
    </div>
</div>

<div class="faq-item">
    <button class="faq-question">
        Can I customize the sweetness and ice level?
        <span>⌄</span>
    </button>

    <div class="faq-answer">
        Absolutely! Our ordering system allows you to select your
        preferred cup size and ice modifications (Normal, Less Ice,
        or No Ice) directly on the Quick View menu before adding
        the drink to your tray.
    </div>
</div>
    </section>


    <section class="faq-contact-grid">

    <!-- LEFT CARD -->

    <div class="faq-contact-card">

        <h2>
            Still have questions?
        </h2>

        <p>
            Our baristas are here to help.
            Reach out and we'll get back to you
            as soon as possible.
        </p>

        <div class="faq-contact-links">

            <a href="#">
                📞 +63 954 425 8134
            </a>

            <a
                href="https://www.facebook.com/"
                target="_blank"
            >
                💬 G'S Coffee Facebook
            </a>

        </div>

    </div>


    <!-- RIGHT CARD -->

    <div class="faq-location-card">

        <div class="faq-location-icon">
            📍
        </div>

        <h2>
            Visit the Shop
        </h2>

        <p>
            Experience the aroma in person at
            our coffee shop in Manila.
        </p>

        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.59697455046!2d120.99720669999999!3d14.565024899999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c9004c326eeb%3A0xe62e0edaf9a49cdf!2zR-KAmXMgQ29mZmVl!5e0!3m2!1sen!2sph!4v1780918759789!5m2!1sen!2sph"
            width="100%"
            height="180"
            style="border:0;border-radius:12px;"
            allowfullscreen=""
            loading="lazy"
        ></iframe>

        <strong>
            2534 Singalong St., Manila, Philippines
        </strong>

        <br><br>

        <a
            href="https://maps.app.goo.gl/CJSM89i4ayEvYNhG7"
            target="_blank"
            class="faq-directions-btn"
        >
            Get Directions
        </a>

    </div>

</section>
</div>

<script>
document.querySelectorAll(".faq-question").forEach(btn => {

    btn.addEventListener("click", () => {

        const item = btn.parentElement;

        item.classList.toggle("active");

    });

});
</script>



<?php include 'footer.php'; ?>