<?php
    $form = isset($_GET['form']) ? $_GET['form'] : 'login';
    $loggedOut = isset($_GET['logout']) && $_GET['logout'] === 'success';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login and Register</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,400;0,700;0,900;1,400;1,900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap"
        rel="stylesheet"
    />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
    />

    <?php if ($loggedOut): ?>
    <style>
        #logoutToast { display: block; }
    </style>
    <meta http-equiv="refresh" content="3;url=login_register.php?form=login">
    <?php endif; ?>

    <style>
        :root {
            --color-primary:           #b70048;
            --color-primary-container: #ff7290;
            --color-on-primary:        #ffeff0;
            --color-on-primary-fixed:  #000000;
            --color-secondary:         #006668;
            --color-secondary-container: #52f9fc;
            --color-on-secondary:      #c0feff;
            --color-tertiary-container:#fdd828;
            --color-on-tertiary-container: #5b4c00;
            --color-surface:           #f5f6f7;
            --color-surface-container-lowest: #ffffff;
            --color-surface-container-low: #eff1f2;
            --color-surface-container: #e6e8ea;
            --color-surface-container-high: #e0e3e4;
            --color-on-surface:        #2c2f30;
            --color-on-surface-variant:#595c5d;
            --color-outline-variant:   #abadae;
            --color-error:             #b31b25;
            --color-ink:               #000000;
            --color-inverse-primary:   #ff4e7c;

            --font-headline: 'Epilogue', sans-serif;
            --font-body:     'Plus Jakarta Sans', sans-serif;

            --shadow-comic:  8px 8px 0px 0px #000000;
            --shadow-small:  4px 4px 0px 0px #000000;
            --border-ink:    4px solid #000000;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            overflow: hidden; 
        }

        body {
            font-family: var(--font-body);
            background-color: var(--color-surface);
            color: var(--color-on-surface);
            display: flex;
            flex-direction: column;
        }

        .ben-day-dots {
            background-image: radial-gradient(#000000 1px, transparent 0);
            background-size: 8px 8px;
            opacity: 0.1;
            pointer-events: none;
        }

        .text-stroke {
            -webkit-text-stroke: 1.5px #000000;
        }

        .shadow-comic {
            box-shadow: var(--shadow-comic);
        }

        .shadow-small {
            box-shadow: var(--shadow-small);
        }

        /* header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            background-color: #ffffff;
            border-bottom: 4px solid #000000;
            box-shadow: 6px 6px 0px 0px #000000;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: center; /* name centered */
        }

        /* Brand name styled like "SEOUL POP" from the reference image */
        .brand-name {
            font-family: 'Epilogue', sans-serif;
            font-size: 2.2rem;
            font-weight: 900;
            font-style: italic;
            letter-spacing: -0.05em; 
            color: #000000; 
            text-transform: none;
            text-decoration: none;
            text-shadow: 4px 4px 0px var(--color-tertiary-container); 
            user-select: none;
        }

        /* MAIN  (fills remaining height) */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            padding-top: 100px;
            position: relative;
            overflow: hidden;
        }

        /* Background floating decorative elements*/
        .bg-decor {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .bg-box {
            position: absolute;
            border: 3px solid #000000;
            font-family: var(--font-headline);
            font-weight: 900;
            font-style: italic;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.05em;
            padding: 6px 10px;
        }

        /* Coloured square blocks */
        .bg-block {
            position: absolute;
            border: 3px solid #000000;
        }

        /* Speech / bubble text tags */
        .bubble-tag {
            position: absolute;
            border: 3px solid #000000;
            font-family: var(--font-headline);
            font-weight: 900;
            font-style: italic;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 9px 16px;
            white-space: nowrap;
        }

        /* Individual decorative pieces */
        .decor-yellow-tl {
            top: 14%;
            left: 5%;
            width: 100px;
            height: 100px;
            background: #fdd828;
            transform: rotate(-8deg);
        }

        .decor-cyan-tr {
            top: 10%;
            right: 8%;
            width: 90px;
            height: 90px;
            background: #52f9fc;
            transform: rotate(12deg);
        }

        .decor-pink-br {
            bottom: 14%;
            right: 6%;
            width: 80px;
            height: 80px;
            background: var(--color-primary-container);
            transform: rotate(-6deg);
        }

        .decor-green-bl {
            bottom: 20%;
            left: 4%;
            width: 85px;
            height: 85px;
            background: #a8f0b0;
            transform: rotate(10deg);
        }

        .decor-dot-tl {
            top: 8%;
            left: 18%;
            width: 30px;
            height: 30px;
            background: var(--color-primary);
            border-radius: 50%;
            border: 3px solid #000;
        }

        .decor-dot-br {
            bottom: 12%;
            right: 20%;
            width: 24px;
            height: 24px;
            background: var(--color-secondary);
            border-radius: 50%;
            border: 3px solid #000;
        }

        /* Bubble text stickers */
        .tag-secure {
            top: 22%;
            left: 6%;
            background: #52f9fc;
            color: #000;
            box-shadow: 3px 3px 0 #000;
            transform: rotate(-3deg);
        }

        .tag-top-secret {
            bottom: 18%;
            right: 5%;
            background: var(--color-primary);
            color: #fff;
            box-shadow: 3px 3px 0 #000;
            transform: rotate(4deg);
        }

        .tag-members-only {
            top: 15%;
            right: 15%;
            background: var(--color-tertiary-container);
            color: #000;
            box-shadow: 3px 3px 0 #000;
            transform: rotate(-6deg);
        }

        .tag-verified {
            bottom: 25%;
            left: 10%;
            background: #a8f0b0;
            color: #000;
            box-shadow: 3px 3px 0 #000;
            transform: rotate(5deg);
        }

        .tag-k-pop {
            top: 38%;
            left: 3%;
            background: #ff7290;
            color: #000;
            box-shadow: 3px 3px 0 #000;
            transform: rotate(-2deg);
        }

        .tag-authorized {
            bottom: 38%;
            right: 3%;
            background: var(--color-tertiary-container);
            color: #000;
            box-shadow: 3px 3px 0 #000;
            transform: rotate(3deg);
        }

        .tag-need-help {
            top: 5%;
            right: 28%;
            background: #fff;
            color: #000;
            box-shadow: 3px 3px 0 #000;
            transform: rotate(-1deg);
        }

        /* comic grid */
        .comic-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 32px;
            width: 100%;
            max-width: 1100px;
            position: relative;
            z-index: 1;
        }

        @media (min-width: 768px) {
            .comic-grid {
                grid-template-columns: 7fr 5fr;
            }
        }

        /* left panel */
        .left-panel {
            position: relative;
            overflow: hidden;
            border: var(--border-ink);
            background-color: var(--color-surface-container);
            border-radius: 4px;
            box-shadow: var(--shadow-comic);
            min-height: 520px;
            max-height: 520px;
        }

        .left-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            filter: grayscale(0.2) contrast(1.25);
            transition: transform 0.5s ease;
        }

        .left-panel:hover img {
            transform: scale(1.05);
        }

        .left-panel-dots {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .panel-title-burst {
            position: absolute;
            top: 40px;
            left: 40px;
            z-index: 2;
            transform: rotate(-3deg);
            background: var(--color-tertiary-container);
            border: var(--border-ink);
            padding: 16px;
            box-shadow: var(--shadow-comic);
        }

        .panel-title-burst h2 {
            font-family: var(--font-headline);
            font-weight: 900;
            font-style: italic;
            font-size: 2.8rem;
            text-transform: uppercase;
            color: var(--color-on-tertiary-container);
            -webkit-text-stroke: 1.5px #000;
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .panel-sticker {
            position: absolute;
            bottom: 48px;
            right: 48px;
            z-index: 2;
            transform: rotate(12deg);
            background: var(--color-secondary-container);
            border: var(--border-ink);
            border-radius: 50%;
            width: 128px;
            height: 128px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-small);
        }

        .panel-sticker span {
            font-family: var(--font-headline);
            font-weight: 900;
            color: var(--color-secondary);
            text-align: center;
            line-height: 1.2;
            font-size: 0.9rem;
        }

        /* right panel */
        .right-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 24px;
            position: relative;
        }

        /* Header card */
        .form-header-card {
            position: relative;
            background: var(--color-primary-container);
            border: var(--border-ink);
            padding: 28px 32px;
            border-radius: 4px;
            box-shadow: var(--shadow-comic);
            overflow: hidden;
        }

        .form-header-card .card-dots {
            position: absolute;
            inset: 0;
            opacity: 0.2;
        }

        .form-header-card h3 {
            position: relative;
            z-index: 1;
            font-family: var(--font-headline);
            font-weight: 900;
            font-style: italic;
            font-size: 2.2rem;
            text-transform: uppercase;
            color: var(--color-on-primary-container);
            letter-spacing: -0.04em;
            line-height: 1.1;
            margin-bottom: 6px;
        }

        .form-header-card p {
            position: relative;
            z-index: 1;
            font-family: var(--font-body);
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: rgba(77, 0, 26, 0.8);
        }

        /* Form card */
        .form-card {
            position: relative;
            background: var(--color-surface-container-lowest);
            border: var(--border-ink);
            padding: 32px;
            border-radius: 4px;
            box-shadow: var(--shadow-comic);
            overflow: visible;
        }

        /* Form elements */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-family: var(--font-headline);
            font-weight: 900;
            font-style: italic;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--color-on-surface);
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            background: var(--color-surface-container-low);
            border: 2px solid var(--color-on-surface);
            padding: 14px 16px;
            border-radius: 4px;
            outline: none;
            font-family: var(--font-body);
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--color-on-surface);
            transition: border-color 0.15s ease;
        }

        .form-group input:focus {
            border-color: var(--color-primary);
        }

        .form-group input::placeholder {
            color: var(--color-outline-variant);
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            background: var(--color-primary);
            color: var(--color-on-primary);
            font-family: var(--font-headline);
            font-weight: 900;
            font-style: italic;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 16px;
            border: 4px solid #000000;
            box-shadow: 6px 6px 0px 0px #ff7290;
            cursor: pointer;
            transition: transform 0.08s ease, box-shadow 0.08s ease;
            margin-top: 8px;
        }

        .btn-submit:hover {
            transform: scale(1.02);
        }

        .btn-submit:active {
            transform: translate(1px, 1px);
            box-shadow: none;
        }

        /* Register link */
        .register-link-row {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 2px solid rgba(89, 92, 93, 0.2);
            text-align: center;
            font-family: var(--font-body);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .register-link-row a {
            color: var(--color-primary);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 4px;
        }

        .register-link-row a:hover {
            color: var(--color-secondary);
        }

        /* Floating mascot sticker on form card */
        .floating-sticker {
            position: absolute;
            bottom: -20px;
            right: -20px;
            z-index: 10;
            transform: rotate(-12deg);
            background: var(--color-secondary);
            border: var(--border-ink);
            padding: 10px;
            border-radius: 4px;
            box-shadow: var(--shadow-small);
        }

        .floating-sticker .material-symbols-outlined {
            font-size: 2.2rem;
            color: var(--color-secondary-container);
            display: block;
        }

        /* Logout success popup */
        .popup-toast {
            position: fixed;
            top: 90px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--color-tertiary-container);
            color: #000000;
            font-family: var(--font-headline);
            font-weight: 900;
            font-style: italic;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 16px 32px;
            border: 4px solid #000000;        
            box-shadow: 6px 6px 0px 0px #000000; 
            z-index: 999;
            display: none;
            white-space: nowrap;
            background-image: radial-gradient(#00000022 1px, transparent 0);
            background-size: 6px 6px;
         background-color: var(--color-tertiary-container);
        }

        .popup-toast.show {
            display: block;                 
        }

        /*footer*/
        footer {
            background-color: #000000;
            border-top: 4px solid #000000;
            padding: 28px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        /* Left: brand + rights */
        .footer-brand {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 160px;
        }

        .footer-brand-name {
            font-family: var(--font-headline);
            font-weight: 900;
            font-style: italic;
            font-size: 1.2rem;
            color: var(--color-primary);
            text-transform: uppercase;
            letter-spacing: -0.02em;
            text-shadow: 2px 2px 0 var(--color-secondary);
        }

        .footer-rights {
            font-family: var(--font-body);
            font-weight: 700;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Center: quick links */
        .footer-links {
            display: flex;
            gap: 28px;
            list-style: none;
        }

        .footer-links a {
            font-family: var(--font-body);
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.15s, text-decoration-color 0.15s;
            text-decoration: underline;
            text-decoration-color: transparent;
            text-decoration-thickness: 3px;
            text-underline-offset: 3px;
        }

        .footer-links a:hover {
            color: #ffffff;
            text-decoration-color: var(--color-tertiary-container);
        }

        /* Right: social icons */
        .footer-socials {
            display: flex;
            gap: 14px;
            align-items: center;
            min-width: 160px;
            justify-content: flex-end;
        }

        .social-icon {
            width: 36px;
            height: 36px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: border-color 0.15s, color 0.15s, background 0.15s;
            text-decoration: none;
        }

        .social-icon:hover {
            border-color: var(--color-primary);
            color: #fff;
            background: rgba(183, 0, 72, 0.2);
        }

        .social-icon .material-symbols-outlined {
            font-size: 1.1rem;
        }
    </style>

</head>
<body>

    <!--header-->
    <header>
        <h1 class="brand-name">WebName</h1>
    </header>

    <div class="popup-toast" id="logoutToast">
        Logout Successful!
    </div>

    <!--main-->
    <main>

        <!-- Background decorative elements -->
        <div class="bg-decor" aria-hidden="true">

            <!-- Coloured blocks -->
            <div class="bg-block decor-yellow-tl"></div>
            <div class="bg-block decor-cyan-tr"></div>
            <div class="bg-block decor-pink-br"></div>
            <div class="bg-block decor-green-bl"></div>

            <!-- Small dots -->
            <div class="bg-block decor-dot-tl" style="border-radius:50%;"></div>
            <div class="bg-block decor-dot-br" style="border-radius:50%;"></div>

            <!-- Bubble text tags -->
            <span class="bubble-tag tag-secure">Secure</span>
            <span class="bubble-tag tag-top-secret">Fresh!</span>
            <span class="bubble-tag tag-members-only">Members Only</span>
            <span class="bubble-tag tag-verified">✓ Verified</span>
            <span class="bubble-tag tag-k-pop">K-Pop Zone</span>
            <span class="bubble-tag tag-authorized">Authorized ✦</span>
            <span class="bubble-tag tag-need-help">Need Help? ↗</span>

        </div>

        <!-- Comic Grid -->
        <div class="comic-grid">

            <!-- ── Left: Illustration Panel ── -->
            <div class="left-panel">
                <div class="ben-day-dots left-panel-dots"></div>
                <img
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuCIvdHoM31Ls_C950_PjuS_-eSeQfo7maNMyfoEVyCU6Nj8eED09BldxfbbtarlVQZUBJWycowotThvndKzdQk1-S5CLwVEo7flFcfuqljQ96ovaE3jK98kIw174RfN0cS3eGTyUbqU3a-1rR0NsRYi8kJug4a5PF9c5unQgpABGy0M4ncaPNCbFeCR7FwECpoVR57fxfwi-CIyBSx7b5W12ly7jpnD_OfNdSnY_4M9ryLsMZuf-3-hufZU8L5nVW7cqfVjcmZqfG4"
                    alt="Futuristic K-pop convenience store entrance with neon lights"
                />
                <!-- Action burst title -->
                <div class="panel-title-burst">
                    <h2 class="text-stroke">
                        CRUNCHY<br/>VIBES ONLY!
                    </h2>
                </div>
                <!-- Decorative sticker -->
                <div class="panel-sticker">
                    <span>100%<br/>KOREAN</span>
                </div>
            </div>

            <!-- ── Right: Login and Registration Form ── -->
            <div class="right-panel">

            <div id="state-login" style="display: <?php echo ($form === 'login') ? 'block' : 'none'; ?>;">

                <!-- Header card-->
                <div class="form-header-card">
                    <div class="ben-day-dots card-dots"></div>
                    <h3>Member Access</h3>
                    <p>Join the krew</p>
                </div>

                <!-- Form card: Login -->
                <div class="form-card">
                    <form action="includes/login_process.php" method="POST">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            placeholder="CRUNCHMASTER_01"
                        />
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="••••••••"
                        />
                    </div>

                
                    <button class="btn-submit" type="submit">
                        Enter The Store
                    </button>
                    </form>

                    <div class="register-link-row">
                        New here? <a href="?form=register">Register Now</a>
                    </div>

                    <!-- Floating bolt sticker -->
                    <div class="floating-sticker">
                        <span class="material-symbols-outlined"
                              style="font-variation-settings:'FILL' 1;">bolt</span>
                    </div>

                </div>
            </div>

            <div id="state-register" style="display: <?php echo ($form === 'register') ? 'block' : 'none'; ?>;">

                <!-- Form card: Registration -->
                <!-- Header card -->
                <div class="form-header-card">
                    <div class="ben-day-dots card-dots"></div>
                    <h3>Create Account</h3>
                    <p>Join the krew</p>
                </div>

                <div class="form-card">
                    <form action="includes/register_process.php" method="POST">

                    <div class="form-group">
                        <label for="username">Create Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            placeholder="CRUNCHMASTER_01"
                        />
                    </div>

                    <div class="form-group">
                        <label for="password">Create Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="••••••••"
                        />
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input
                            id="confirm_password"
                            name="confirm_password"
                            type="password"
                            placeholder="••••••••"
                        />
                    </div>

                    <button class="btn-submit" type="submit">
                        Enter The Store
                    </button>
                    </form>

                    <div class="register-link-row">
                        Have an account? <a href="?form=login">Login Now</a>
                    </div>

                    <!-- Floating bolt sticker -->
                    <div class="floating-sticker">
                        <span class="material-symbols-outlined"
                              style="font-variation-settings:'FILL' 1;">bolt</span>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!--footer-->
    <footer>

        <!-- Far left: brand -->
        <div class="footer-brand">
            <span class="footer-brand-name">WebName</span>
            <span class="footer-rights">© 2024 WebName Ltd.<br/>All rights reserved.</span>
        </div>

        <!-- Center: quick links -->
        <!--ul class="footer-links">
            <li><a href="dashboard.html">Dashboard</a></li>
            <li><a href="inventory.html">Inventory</a></li>
            <li><a href="orders.html">Orders</a></li>
            <li><a href="users.html">Users</a></li>
        </ul-->

        <!-- Far right: social icons -->
        <div class="footer-socials">
            <a href="#" class="social-icon" title="Instagram">
                <span class="material-symbols-outlined">photo_camera</span>
            </a>
            <a href="#" class="social-icon" title="Twitter / X">
                <span class="material-symbols-outlined">alternate_email</span>
            </a>
            <a href="#" class="social-icon" title="YouTube">
                <span class="material-symbols-outlined">smart_display</span>
            </a>
            <a href="#" class="social-icon" title="TikTok">
                <span class="material-symbols-outlined">music_note</span>
            </a>
        </div>

    </footer>

</body>

</html>