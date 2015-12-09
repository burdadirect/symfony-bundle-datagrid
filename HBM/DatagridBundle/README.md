# hbm_lottery_calendar

## Team

### Developers
Christian Puchinger - puchinger@playboy.de

## Installation

- `composer install`
- `php app/console doctrine:schema:update --force --dump-sql`
- `npm install`
- `bower install`
- `gulp install`
- `gulp dev|prod`

or run `deploy.sh`

## Deployment auf _heroku_ as app _hbm-lottery-calendar_ and remote alias _hbm_

- `heroku create hbm-lottery-calendar --buildpack https://github.com/ddollar/heroku-buildpack-multi.git --region eu --remote hbm`
    - `heroku git:remote -r hbm -a hbm-lottery-calendar` (optional)
    - `heroku buildpacks:set https://github.com/ddollar/heroku-buildpack-multi.git -r hbm` (optional)
- `heroku config:set SYMFONY__HBMLC__ENV=prod` or better use `. ./env.set.sh heroku` for initial setting the environment varialbes
- Add a file named `.buildpacks` to define the desired multiple buildpacks to use 
- Add a file named `Procfile` to define the webroot depening on your buildpack and desired webserver: `web: bin/heroku-php-apache2 web/`
- `heroku config:add TZ="Europe/Berlin"`
- `git push hbm master`

### Notes
Tried setting the timezone with a `.user.ini` file. => not working
Tried setting the timezone in php-config in `composer.json`. => not working
Tried setting environment varialbes `TZ` and `TIMEZONE`. => not working (at least not out of the box)
Added `date_default_timezone_set` to `app.php` and `app_dev.php`. => worked
Other possibility: Setting the timezone in the `.htaccess`file. => not tested

## Usage


### Without JQUERY
```
<script type="text/javascript">
var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

window[eventMethod](messageEvent,function(event) {
    var key = event.message ? "message" : "data";
    var data = event[key];

    document.getElementById('adventskalender-2015').style.height = data.height + 'px';
},false);
</script>
<iframe frameborder="0" id="adventskalender-2015" src="http://playboy-adventskalender.herokuapp.com/calendar/show/1" style="max-width:1040px; width:100%;"></iframe>
```

### With JQUERY
```
  <script type="text/javascript">
  $(window).on('message', function(event) {
      $('#adventskalender-2015').height(event.originalEvent.data.height);
  },false);
  </script>
  <iframe frameborder="0" id="adventskalender-2015" src="http://playboy-adventskalender.herokuapp.com/calendar/show/1" style="max-width:1040px; width:100%;"></iframe>
```
