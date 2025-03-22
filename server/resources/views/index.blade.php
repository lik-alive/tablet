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
                <section id='op_v'>
                    <div>Овраг подпольщиков</div>
                    <div class='opacity-75'>В сторону Ж/д вокзала</div>
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
                    <div class='opacity-75'>В сторону Ж/д вокзала</div>
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
                <section id='op_u'>
                    <div>Овраг подпольщиков</div>
                    <div class='opacity-75'>В сторону Управленческого</div>
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
                    <div class='opacity-75'>В сторону Управленческого</div>
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
                <div class="d-flex justify-content-between mt-3">
                    <button id='towork' class='btn btn-outline-info btn-lg shadow-none' tabindex='-1'><span class="bi-arrow-left"></span> Работа</button>
                    <button id='tohome' class='btn btn-outline-info btn-lg shadow-none'>Дом <span class="bi-arrow-right"></span></button>
                </div>
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
         * Redraw transport info (with time offset)
         */
        function redrawTransportInfo() {
            const nowTime = (new Date()).getTime();
            for (const stop in stopInfo) {
                for (const route in stopInfo[stop].routes) {
                    const routeHtml = $(`#transport-container #${stop} div[name=${route}]`);
                    if (!routeHtml.length) continue;

                    const updatedAt = stopInfo[stop].updatedAt;
                    const offset = (nowTime - updatedAt) / 1000 / 60;

                    let ready = false;

                    const arrives = stopInfo[stop].routes[route];
                    const arrivesHtml = arrives.sort((a, b) => a - b).map(a => a - offset).filter(a => a > 0).map(a => {
                        const min = Math.floor(a);
                        if (a < 12) a = `<span class='late'>${min} мин.</span>`;
                        else if (a > 22) a = `<span class='future'>${min} мин.</span>`;
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
        function loadStopInfo(stop, isUrgent = false) {
            return $.get('transport', {
                stop,
                isUrgent: isUrgent ? 1 : 0
            }, function(data) {
                if (data === '') return;
                stopInfo[stop] = JSON.parse(data);
            }).promise();
        }

        /**
         * Update transport data
         */
        async function transportInfo() {
            let urgentUpdate = [];
            if ($('#towork').hasClass('active')) urgentUpdate = ['op_v', 'su_v'];
            else if ($('#tohome').hasClass('active')) urgentUpdate = ['op_u', 'su_u'];

            // Update each stop
            for (const key in stopInfo) {
                const isUrgent = urgentUpdate.includes(key);
                await loadStopInfo(key, isUrgent);
            }

            // Redraw info
            redrawTransportInfo();
        }

        /**
         * Init after jquery is ready
         */
        $(document).ready(function() {
            // Process weather
            weatherInfo();
            setInterval(weatherInfo, 15 * 60 * 1000);

            // Process transport
            transportInfo();
            setInterval(transportInfo, 2 * 60 * 1000);
            setInterval(redrawTransportInfo, 30 * 1000);

            $('#transport .btn').click(function() {
                if (this.id === 'tohome') $('towork').removeClass('active');
                else $('tohome').removeClass('active');
                $(this).toggleClass('active');

                // Load info
                if ($(this).hasClass('active')) transportInfo();
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