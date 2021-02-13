Plugin for ocStore for Clobucks.com webmasters
==============

### Description
The plugin allows you to create your own online stores based on the products provided by the partner program
Clobucks.com. The base for the online store is the [ocStore] engine (http://myopencart.ru/).
Thanks to this plugin, you can promote and earn on your stores with Clobucks.com.
You just need to [register] (http://clobucks.com/register.php?utm_source=github&utm_medium=link&utm_campaign=plugin), add your site and start earning.

### Installation

In the file `/ catalog / controller / checkout / confirm.php` it is necessary, after the line:

`` `php
$ this-> session-> data ['order_id'] = $ this-> model_checkout_order-> addOrder ($ data);
``

Add two lines:

`` `php
$ this-> load-> model ('clobucks / Order');
$ this-> model_clobucks_Order-> apiSetOrder ($ this-> customer, $ this-> cart-> getProducts (), $ this-> session-> data ['order_id']);
``

In the file `admin / view / template / sale / order_list.tpl`, add the line (Add after <div class =" buttons "> - about 18 lines):
`` 'html
<a onclick="$('#form').attr('action','<?php echo $sync; ?> '); $ (' # form '). attr (' target ',' _self ') ; $ ('# form'). submit (); " class = "button"> Synchronize Orders </a>
``

It is necessary to overwrite the file `admin / controller / sale / order.php`, or, if the file is changed, add a function,
after the update function:
`` `php
public function sync () {
    $ this-> language-> load ('sale / order');
    $ this-> document-> setTitle ($ this-> language-> get ('heading_title'));

    $ this-> load-> model ('sale / order');

    if (isset ($ this-> request-> post ['selected'])) {
        $ this-> load-> model ('clobucks / Order');
        $ this-> load-> model ('clobucks / ClobucksShop');

        foreach ($ this-> request-> post ['selected'] as $ order_id) {

            $ orderId = $ this-> model_clobucks_Order-> getOrder ($ order_id);
            if (is_null ($ orderId)) continue;

            $ this-> model_clobucks_ClobucksShop-> apiSync ($ orderId, $ order_id);
        }

        $ this-> session-> data ['success'] = $ this-> language-> get ('text_success');

        $ url = '';

        if (isset ($ this-> request-> get ['filter_order_id'])) {
            $ url. = '& filter_order_id ='. $ this-> request-> get ['filter_order_id'];
        }

        if (isset ($ this-> request-> get ['filter_customer'])) {
            $ url. = '& filter_customer ='. urlencode (html_entity_decode ($ this-> request-> get ['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset ($ this-> request-> get ['filter_order_status_id'])) {
            $ url. = '& filter_order_status_id ='. $ this-> request-> get ['filter_order_status_id'];
        }

        if (isset ($ this-> request-> get ['filter_total'])) {
            $ url. = '& filter_total ='. $ this-> request-> get ['filter_total'];
        }

        if (isset ($ this-> request-> get ['filter_date_added'])) {
            $ url. = '& filter_date_added ='. $ this-> request-> get ['filter_date_added'];
        }

        if (isset ($ this-> request-> get ['filter_date_modified'])) {
            $ url. = '& filter_date_modified ='. $ this-> request-> get ['filter_date_modified'];
        }

        if (isset ($ this-> request-> get ['sort'])) {
            $ url. = '& sort ='. $ this-> request-> get ['sort'];
        }

        if (isset ($ this-> request-> get ['order'])) {
            $ url. = '& order ='. $ this-> request-> get ['order'];
        }

        if (isset ($ this-> request-> get ['page'])) {
            $ url. = '& page ='. $ this-> request-> get ['page'];
        }

        $ this-> redirect ($ this-> url-> link ('sale / order', 'token ='. $ this-> session-> data ['token']. $ url, 'SSL'));
    }

    $ this-> getList ();
}
``

### License 

MIT

(c) Clobucks.com 2013
