<?php
$title = 'Tablet';
$description = 'A tablet-oriented service for weather and transport monitoring';
?>

<!DOCTYPE html>
<html lang="ru" class='h-100'>

<head xmlns:og="http://ogp.me/ns#">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="icon" href="favicon.ico" />

    <title><?php echo $title ?></title>

    <!-- Meta info -->
    <meta name="description" content="<?php echo $description ?>" />
    <meta property="og:title" content="<?php echo $title ?>" />
    <meta property="og:url" content="{{ env('SITE_URL') }}" />
    <meta property="og:description" content="<?php echo $description ?>" />
    <meta property="og:image" content="{{ env('SITE_URL') }}/logo.jpg" />
    <meta property="og:site_name" content="<?php echo $title ?>" />
    <meta property="og:type" content="website" />

    <!-- Styles -->
    <link rel="stylesheet" href="plugins/bootstrap-icons.min.css">
    <link href="plugins/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body class='d-flex flex-column h-100'>
    <main class='h-100 d-flex'>
        <!-- Weather -->
        <div id='weather'>
            <div id='weather-container'></div>
        </div>

        <!-- Transport -->
        <div id='transport'>
            <div id='transport-container'>
                <section id='op_u'>
                    <div>Овраг подпольщиков</div>
                    <div class='small opacity-75'>В сторону Управленческого</div>
                    <div class='routes'>
                        <div class='row'>
                            <div class='col-12' name='50'>
                                <label>50:</label>
                                <span class='arrives'></span>
                            </div>
                        </div>
                    </div>
                </section>
                <section id='su_u'>
                    <div>Аэрокосмический университет</div>
                    <div class='small opacity-75'>В сторону Управленческого</div>
                    <div class='routes'>
                        <div class='row'>
                            <div class='col-12' name='1'>
                                <label>1:</label>
                                <span class='arrives'></span>
                            </div>
                            <div class='col-12' name='84'>
                                <label>84:</label>
                                <span class='arrives'></span>
                            </div>
                        </div>
                    </div>
                </section>
                <section id='op_v'>
                    <div>Овраг подпольщиков</div>
                    <div class='small opacity-75'>В сторону Ж/д вокзала</div>
                    <div class='routes'>
                        <div class='row'>
                            <div class='col-12' name='50'>
                                <label>50:</label>
                                <span class='arrives'></span>
                            </div>
                        </div>
                    </div>
                </section>
                <section id='su_v'>
                    <div>Аэрокосмический университет</div>
                    <div class='small opacity-75'>В сторону Ж/д вокзала</div>
                    <div class='routes'>
                        <div class='row'>
                            <div class='col-12' name='1'>
                                <label>1:</label>
                                <span class='arrives'></span>
                            </div>
                            <div class='col-12' name='47'>
                                <label>47:</label>
                                <span class='arrives'></span>
                            </div>
                            <div class='col-12' name='47'>
                                <label>67:</label>
                                <span class='arrives'></span>
                            </div>
                            <div class='col-12' name='84'>
                                <label>84:</label>
                                <span class='arrives'></span>
                            </div>
                        </div>
                    </div>
                </section>
                <button id='towork' class='btn btn-outline-info btn-lg shadow-none me-5' tabindex='-1'>Едем на работу</button>
                <button id='tohome' class='btn btn-outline-info btn-lg shadow-none'>Едем домой</button>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class='footer'>
        <?php echo date('Y') ?>-2025 — <?php echo $title ?> ©LIK
    </footer>

    <!-- Scripts -->
    <script src="plugins/bootstrap.bundle.min.js"></script>
    <script src="plugins/jquery-3.7.1.min.js"></script>

    <script>
        let weatherInfoInterval = null;
        let transportInfoInterval = null;
        const stopInfo = {
            'op_u': {
                updatedAt: 0,
                routes: {}
            },
            'su_u': {
                updatedAt: 0,
                routes: {}
            },
            'op_v': {
                updatedAt: 0,
                routes: {}
            },
            'su_v': {
                updatedAt: 0,
                routes: {}
            },
        }

        /**
         * Update weather data
         */
        function weatherInfo() {
            $.get('weather', function(data) {
                if (data === '') return;
                $('#weather-container').html(data);
            })
        }

        /**
         * Reload weather
         */
        function reloadWeather() {
            if (weatherInfoInterval) clearInterval(weatherInfoInterval);
            // Immediate load
            weatherInfo();
            // Regular update
            weatherInfoInterval = setInterval(weatherInfo, 15 * 60 * 1000);
        }

        /**
         * Redraw transport info (with time offset)
         */
        function redrawTransportInfo() {
            const nowTime = (new Date()).getTime();
            for (const stop in stopInfo) {
                for (const route in stopInfo[stop].routes) {
                    const routeHtml = $(`#transport-container #${stop} div[name=${route}]`);
                    if (!route.length) continue;

                    const updatedAt = stopInfo[stop].updatedAt;
                    const offset = (nowTime - updatedAt) / 1000 / 60;

                    let ready = false;

                    const arrives = stopInfo[stop].routes[route];
                    const arrivesHtml = arrives.sort((a, b) => a - b).map(a => a - offset).filter(a => a > 0).map(a => {
                        const min = Math.floor(a);
                        if (a > 20 || a < 10) a = `<span class='bad'>${min} мин.</span>`;
                        else {
                            a = `<span class='good'>${min} мин.</span>`;

                            if ($('#towork').hasClass('active') && (stop === 'op_v' || (stop === 'su_v' && ['47'].includes(route)))) ready = true;
                            if ($('#tohome').hasClass('active') && (stop === 'op_u' || (stop === 'su_u' && ['1', '84'].includes(route)))) ready = true;
                        }
                        return a;
                    }).join(", ");

                    routeHtml.find('.arrives').html(arrivesHtml);

                    // Play ready sound
                    if (ready) {
                        gtg();
                        $('#transport .btn').removeClass('active');
                    }
                }
            }
        }

        /**
         * Load stop info
         */
        function loadStopInfo(stop) {
            return $.get('transport', {
                stop
            }, function(data) {
                if (data === '') return;
                const jdata = JSON.parse(data);
                const nowTime = (new Date()).getTime();
                for (const route in jdata) {
                    stopInfo[stop].updatedAt = nowTime;
                    stopInfo[stop].routes[route] = jdata[route];
                }
            }).promise();
        }

        /**
         * Update transport data
         */
        async function transportInfo() {
            // Skip updating by nights
            if ((new Date()).getHours() < 5) return;

            let urgentUpdate = [];
            if ($('#towork').hasClass('active')) urgentUpdate = ['op_v', 'su_v'];
            else if ($('#tohome').hasClass('active')) urgentUpdate = ['op_u', 'su_u'];

            for (const key in stopInfo) {
                const nowTime = (new Date()).getTime();
                if (urgentUpdate.includes(key) || (nowTime - stopInfo[key].updatedAt > 10 * 60 * 1000)) {
                    await loadStopInfo(key);
                    redrawTransportInfo();
                }
            }
        }

        /**
         * Reload transport
         */
        function reloadTransport() {
            if (transportInfoInterval) clearInterval(transportInfoInterval);
            // Immediate load
            transportInfo();
            // Regular update
            transportInfoInterval = setInterval(transportInfo, 2 * 60 * 1000);
        }

        /**
         * Init after jquery is ready
         */
        $(document).ready(function() {
            // Small protection from overcrawling
            const searchParams = new URLSearchParams(window.location.search);
            if (!searchParams.has('lik')) return;

            // Process weather
            // reloadWeather();

            // Process transport
            // reloadTransport();
            redrawTransportInfo();
            setInterval(redrawTransportInfo, 30 * 1000);

            $('#transport .btn').click(function() {
                if (this.id === 'tohome') $('towork').removeClass('active');
                else $('tohome').removeClass('active');
                $(this).toggleClass('active');
                reloadTransport();
            });
        });

        /**
         * Play ready sound
         */
        function gtg() {
            const audio = new Audio('gtg.mp3');
            audio.play();
        }
    </script>
</body>

</html>