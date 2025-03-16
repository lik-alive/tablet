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
                                <span class='times'></span>
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
                                <span class='times'></span>
                            </div>
                            <div class='col-12' name='84'>
                                <label>84:</label>
                                <span class='times'></span>
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
                                <span class='times'></span>
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
                                <span class='times'></span>
                            </div>
                            <div class='col-12' name='47'>
                                <label>47:</label>
                                <span class='times'></span>
                            </div>
                            <div class='col-12' name='47'>
                                <label>67:</label>
                                <span class='times'></span>
                            </div>
                            <div class='col-12' name='84'>
                                <label>84:</label>
                                <span class='times'></span>
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
        /**
         * Update weather data
         */
        function weatherUpdate() {
            $.get('weather', function(data) {
                if (data === '') return;
                $('#weather-container').html(data);
            })
        }

        /**
         * Update stop data
         */
        function updateStop(stop) {
            return $.get('transport', {
                stop
            }, function(data) {
                if (data === '') return;
                const jdata = JSON.parse(data);
                for (const key in jdata) {
                    const route = $(`#transport-container #${stop} div[name=${key}]`);
                    if (!route.length) continue;

                    const values = jdata[key].sort();
                    let ready = false;
                    const times = values.sort((a, b) => a - b).map(x => {
                        const min = Math.floor(x);
                        if (x > 20 || x < 10) x = `<span class='bad'>${min} мин.</span>`;
                        else {
                            x = `<span class='good'>${min} мин.</span>`;

                            if ($('#towork').hasClass('active') && (stop === 'op_v' || stop === 'su_v')) ready = true;
                            if ($('#tohome').hasClass('active') && (stop === 'op_u' || (stop === 'su_u' && key === '1'))) ready = true;
                        }
                        return x;
                    }).join(", ");

                    // Play ready sound
                    if (ready) {
                        gtg();
                        $('#towork').removeClass('active');
                        $('#tohome').removeClass('active');
                    }
                    route.find('.times').html(times);
                }
            }).promise();
        }

        let lastUpdateAt = null;

        /**
         * Update transport data
         */
        async function transportUpdate() {
            if ($('#towork').hasClass('active')) {
                await updateStop('op_v');
                await updateStop('su_v');
            } else if ($('#tohome').hasClass('active')) {
                await updateStop('op_u');
                await updateStop('su_u');
            } else {
                const now = new Date();

                // Skip updating by nights
                if (now.getHours() < 5) return;

                const nowTime = now.getTime();
                console.log(nowTime - lastUpdateAt);
                if (!lastUpdateAt || nowTime - lastUpdateAt > 9 * 60 * 1000) {
                    lastUpdateAt = nowTime;
                    await updateStop('op_v');
                    await updateStop('su_v');
                    await updateStop('op_u');
                    await updateStop('su_u');
                }
            }
        }

        /**
         * Play ready sound
         */
        function gtg() {
            const audio = new Audio('gtg.mp3');
            audio.play();
        }

        /**
         * Init after jquery is ready
         */
        $(document).ready(function() {
            // Process weather
            weatherUpdate();
            setInterval(weatherUpdate, 15 * 60 * 1000);

            // Process transport
            transportUpdate();
            setInterval(transportUpdate, 2 * 60 * 1000);

            $('#tohome').click(function() {
                $('#towork').removeClass('active');
                $(this).toggleClass('active');
            });

            $('#towork').click(function() {
                $('#tohome').removeClass('active');
                $(this).toggleClass('active');
            });
        });
    </script>
</body>

</html>