# Tablet

## Info

**Tablet-oriented service for weather and transport monitoring**

This is a personal service that provides monitoring of weather and transport conditions outside. The service includes sound notifications about incoming transport based on a planning destination (to work or home).

The service was deployed on an old obsolete barely alive tablet (Texet TX-7055HD) and supported since 2025.

## Table of Contents
- [Features](#features)
  - [Common](#common)
  - [Functionality](#functionality)
- [Installation](#installation)
  - [For development](#for-development)
  - [For production](#for-production)
- [License](#license)

## Features

### Common
- Client: Laravel's Blade v1.4 + Bootstrap v5.1.3
- Server: Slim v4
- Tablet-oriented

### Functionality
- Automatically updated weather (Yandex-weather)
- Automatically updated transport information (Yandex-transport)
- Sound notifications for incoming transport

## Installation

### For development

1. Create `.env.dev`
   
2. Deploy
```sh
./deploy.sh
```

### For production

1. Create `.env.prod`
   
2. Deploy
```sh
./deploy.sh --prod
```

## License

MIT License