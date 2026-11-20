<footer class="pp-footer">

        <div class="pp-footer-main">

            <!-- IDENTIDAD DE MARCA -->
            <div class="pp-footer-brand">

                <a href="{{ route('home') }}" class="pp-footer-logo">
                    <img src="{{ asset('assets/images/logo-completo-blanco.png') }}"
                        alt="Project Pace">
                </a>

                <p class="pp-footer-slogan">
                    TRAIN WITH PRECISION.<br>
                    ELEVATE YOUR PERFORMANCE.
                </p>

            </div>

            <!-- NAVEGACIÓN -->
            <div class="pp-footer-column">

                <h3>EXPLORE</h3>

                <ul>
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('home') }}#latest-drop">Latest Drop</a></li>
                    <li><a href="{{ route('shop') }}">Shop</a></li>
                    <li><a href="{{ route('archive') }}">Archive</a></li>
                </ul>

            </div>

            <!-- REDES Y CONTACTO -->
            <div class="pp-footer-column">

                <h3>CONNECT</h3>

                <ul>
                    <li>
                        <a href="https://www.instagram.com/projectpace_/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="pp-social-link">

                            <svg viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                aria-hidden="true">

                                <rect x="3" y="3"
                                    width="18" height="18"
                                    rx="5"/>

                                <circle cx="12" cy="12" r="4"/>

                                <circle cx="17.5" cy="6.5"
                                        r="1"
                                        fill="currentColor"
                                        stroke="none"/>
                            </svg>

                            Instagram &#8599;

                        </a>
                    </li>

                    <li>
                        <a href="https://www.instagram.com/projectpace_/"
                        target="_blank"
                        rel="noopener noreferrer">
                            Contact
                        </a>
                    </li>
                </ul>

                <p class="pp-footer-location">
                    Santa Cruz, Bolivia
                </p>

            </div>

        </div>

        <!-- PARTE INFERIOR -->
        <div class="pp-footer-bottom">

            <p>
                &copy; 2026 Project Pace.
                All rights reserved.
            </p>

            <a href="#top"
            class="pp-back-top"
            aria-label="Volver al inicio de la página">
                BACK TO TOP &uarr;
            </a>

        </div>

    </footer>
