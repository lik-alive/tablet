"""Process yandex transport requests"""
import json
from yandex_transport_webdriver_api import YandexTransportProxy
import time
import logging

def getInfo(url):
    """Get info of arriving routes"""
    
    # Get transport info
    try:
        proxy = YandexTransportProxy('ytproxy', 3002)
        data = proxy.get_stop_info(url)
    except:
        logging.basicConfig(filename='ytapi.log', level=logging.INFO, format='%(asctime)s %(message)s')
        logging.getLogger('').error('Yandex Transport error')
        return {}
    
    print(data)
    
    # Process transport info
    result = {}
    now = time.time()        
    transports = data['data']['transports']
    for tr in transports:
        name = tr['name']
        events = tr['threads'][0]['BriefSchedule']['Events']
        times = []
        for ev in events:
                if 'Estimated' in ev:
                    arrivedAt = float(ev['Estimated']['value'])
                    times.append((arrivedAt - now) / 60)
        
        result[name] = times
            
    return result


def app(environ, start_response):
    """Setup simple web server"""

    stops = {
        "op_u": "https://yandex.ru/maps/51/samara/stops/stop__10001296/?ll=50.162961%2C53.219656&tab=overview&z=18.67",
        "op_v": "https://yandex.ru/maps/51/samara/stops/stop__10001295/?ll=50.162961%2C53.219656&tab=overview&z=18.67",
        "su_u": "https://yandex.ru/maps/51/samara/stops/stop__10001233/?indoorLevel=1&ll=50.180398%2C53.212652&tab=overview&z=18.4",
        "su_v": "https://yandex.ru/maps/51/samara/stops/stop__10001234/?indoorLevel=1&ll=50.180398%2C53.212652&tab=overview&z=18.4",
    }
    
    name = environ['PATH_INFO'][1:]
        
    info = getInfo(stops[name])
    data = json.dumps(info).encode('utf-8')
    
    response_headers = [
        ('Content-type', 'application/json'),
        ('Content-Length', str(len(data)))
    ]
    start_response('200 OK', response_headers)
    return [data]