ocstore-plugin
==============


В файл /catalog/controller/checkout/confirm.php необходимо, после строки:
$this->session->data['order_id'] = $this->model_checkout_order->addOrder($data);

Добавить две строки:
$this->load->model('clobucks/Order');
$this->model_clobucks_Order->apiSetOrder($this->customer,$this->cart->getProducts(),$this->session->data['order_id']);

В файл admin/view/template/sale/order_list.tpl, добавить строку:
      		<a onclick="$('#form').attr('action', '<?php echo $sync; ?>'); $('#form').attr('target', '_self'); $('#form').submit();" class="button">Синхронизировать заказы</a> 
Добавить после <div class="buttons"> (приблизительно 18 строка)


Необходимо перезаписать файл admin/controller/sale/order.php , либо, если файд изменен, добавить функцию, после функции update:
	public function sync() {
		$this->language->load('sale/order');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/order');
		
    	if (isset($this->request->post['selected'])) {
    		$this->load->model('clobucks/Order');
    		$this->load->model('clobucks/ClobucksShop');
    		
			foreach ($this->request->post['selected'] as $order_id) {
				
				$orderId = $this->model_clobucks_Order->getOrder($order_id);
				if(is_null($orderId)) continue;

				$this->model_clobucks_ClobucksShop->apiSync($orderId, $order_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}
			
			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}
												
			if (isset($this->request->get['filter_order_status_id'])) {
				$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
			}
			
			if (isset($this->request->get['filter_total'])) {
				$url .= '&filter_total=' . $this->request->get['filter_total'];
			}
						
			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}
			
			if (isset($this->request->get['filter_date_modified'])) {
				$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
			}
													
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->redirect($this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	}

    	$this->getList();			
	}	