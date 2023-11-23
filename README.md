# Puller
A laravel package for broadcasting events using long-polling

## Installation
```bash
composer require as247/puller
php artisan migrate
```


## Usage
### 1. Update broadcasting driver in .env file
```dotenv
BROADCAST_DRIVER=puller
```

### 2. Configure Echo
```javascript
import Echo from 'laravel-echo'
import Puller from 'puller-js'
window.Echo = new Echo({
    broadcaster: Puller.echoConnect,
});
```

