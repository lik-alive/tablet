"""Process yandex transport requests"""
import json
from yandex_transport_webdriver_api import YandexTransportProxy
import time
import logging
import datetime
import os.path

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

def monitor():
    stops = {
        "op_u": "https://yandex.ru/maps/51/samara/stops/stop__10001296/?ll=50.162961%2C53.219656&tab=overview&z=18.67",
        "op_v": "https://yandex.ru/maps/51/samara/stops/stop__10001295/?ll=50.162961%2C53.219656&tab=overview&z=18.67",
        "su_u": "https://yandex.ru/maps/51/samara/stops/stop__10001233/?indoorLevel=1&ll=50.180398%2C53.212652&tab=overview&z=18.4",
        "su_v": "https://yandex.ru/maps/51/samara/stops/stop__10001234/?indoorLevel=1&ll=50.180398%2C53.212652&tab=overview&z=18.4",
    }
    
    while True:
        # Sleep at night
        if (datetime.datetime.now().hour < 5):
            time.sleep(30)
            continue
        
        # Create update list
        now = time.time() * 1000
        updateList = []
        for stop in stops:
            infoFile = f'/stops/{stop}.json'
            flagFile = f'/stops/{stop}.fl'
            
            # First load
            if not os.path.isfile(infoFile):
                updateList.append(stop)
                continue
            
            # Load old info
            with open(infoFile) as file:
                oldData = json.load(file)
            offset = now - oldData['updatedAt']
            
            # Update urgently
            if os.path.isfile(flagFile) and offset > 2 * 60 * 1000:
                os.remove(flagFile)
                updateList.insert(0, stop)                    
            # Update regularly
            elif offset > 10 * 60 * 1000:
                updateList.append(stop)
            
        # Update info
        for stop in updateList:
            # Get info from Yandex Transport
            info = getInfo(stops[stop])
            
            # Skip empty response
            if not info:
                continue 
            
            # Save to a file
            data = {
                'routes' : info,
                'updatedAt': time.time() * 1000
            }
            with open(infoFile, 'w') as file:
                file.write(json.dumps(data, indent=4, separators=(',', ': ')))
        
        time.sleep(30)

if __name__ == '__main__':
    monitor()