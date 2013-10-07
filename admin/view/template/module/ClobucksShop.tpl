<?= $header; ?>
<link rel="stylesheet" type="text/css" href="view/stylesheet/clobucksshop.css" />
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
            <?= $breadcrumb['separator']; ?><a href="<?= $breadcrumb['href']; ?>"><?= $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <?php if ($error_warning) { ?>
        <div class="warning"><?= $error_warning; ?></div>
    <?php } ?>

    <div class="box">
        <div class="heading">
            <h1><img src="view/image/module.png" alt="" /> <?= $heading_title; ?></h1>
            <div class="buttons">
            <a onclick="$('#form').attr('action', '<?= html_entity_decode($urls["sync"])?>'); $('#form').attr('target', '_self'); $('#form').submit();" class="button">Синхронизировать</a>
            <a onclick="$('#form').submit();" class="button"><?= $button_save; ?></a>
            <a onclick="location = '<?= $cancel; ?>';" class="button"><?= $button_cancel; ?></a></div>
        </div>

        <div class="content">
            <table id="clobucksShop" class="list">
                <thead>
                    <tr>
                        <td class="left auth">Данные авторизации</td>
                        <td class="left">Поставщики</td>
                        <td class="left">Категории товаров</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="left test-auth"  valign="top">
                                <div style="display: none;" id="formNotifies">
                                    <span></span>
                                </div>
                                <div>
                                    <table>
                                    <form action="<?=$action;?>" method="post" id="form">
                                    <tr>
                                        <td>
                                            <label for="login"><?=$trans['login'];?></label>
                                        </td><td>
                                            <input type="text" name="login" id="login" value="<?= isset($login) ? $login : ''; ?>" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label for="password"><?= $trans['password']; ?></label>
                                        </td><td>
                                            <input type="text" name="password" id="password" value="<?= isset($password) ? $password : ''; ?>" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label for="hash"><?= $trans['hash']; ?></label>
                                        </td><td>                                    
                                            <input type="text" name="hash" id="hash" value="<?= isset($hash) ? $hash : ''; ?>" >
                                        </td>
                                    </tr>
                                    </form>
                                    </table>
                            

                            <button id="testConnection">Проверить аутентификацию</button>
                        </td>
                        <td class="left" valign="top">
                            <button id="getSuppliersList">Загрузить список поставщиков</button>
                            <select id="suppliersList"></select>
                            <br><br>
                            <button id="getsupplierProductsList" style="display: none">Загрузить товары поставщика</button>
                        </td>
                        <td class="left">
                            <div style="display: none;" id="supplierCategoriesList"></div>

                            <button id="exportProducts" style="display: none">Экспорт товаров</button>
                            <span id="exportProducts_status"></span>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
            </table>
            <hr/>
            <table id="clobucksShop" class="list">
                <thead>
                    <tr>
                        <td class="left">Доставки в системе ocStore</td>
                        <td class="left">Доставки поставщика</td>
                    </tr>
                </thead>
                <tbody>
                	<?if(is_array($shipping) && count($shipping)):?>
                		<?foreach($shipping as $value):?>
                    <tr>
                        <td class="left related_delivery" id="relate_<?=$value['value']?>">
							<?=$value['name']?>
                        </td>
                        <td class="left" valign="top">

							<select name="relate_delivery" id="<?=$value['value']?>">
                				<?if(is_array($c_shipping) && count($c_shipping)):?>
                						<option value="0">Выберите</option>
                					<?foreach($c_shipping as $c_value):?>
                						<?$selected = in_array($value['value'],$c_value['selected']) ? "selected='selected'" : null;?>							
										<option value="<?=$c_value['id']?>" <?=$selected?>><?=$c_value['title']?></option>
				                    <?endforeach?>
				                <?endif?> 
				            </select>
                        </td>
                    </tr>
                    	<?endforeach?>
                    <?endif?>                                       
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>            

            <script type="text/javascript" src="view/javascript/jquery-tree/jquery.tree.min.js"></script>
            <script language="javascript" type="text/javascript">
                $('head').append($('<link rel="stylesheet" type="text/css" />').attr('href', 'view/javascript/jquery-tree/jquery.tree.min.css'));
                function setNotify(msg){
                    msg = typeof(msg) === 'undefined' ? '' : msg;
                    var notify = $('div#formNotifies span');

                    notify.text(msg);
                }
                function showFormNotifies(){
                    $('div#formNotifies').show();
                }
                function hideFormNotifies(){
                    $('div#formNotifies').hide();
                }

                function renderSuppliersList(suppliers){
                    var length = suppliers.length;
                    var suppliersSelect = $('#suppliersList');

                    suppliersSelect.empty();

                    for (var i = 0; i < length; i++) {
                    	if(i == 0) {
							suppliersSelect.append($("<option></option>").attr("value", "0").text('Выберите поставщика'));	
                    	}
                        var supplier = suppliers[i];
                        
                        
                        if(supplier.id == "<?=$supplier?>") {
                            suppliersSelect.append($("<option></option>").attr("selected", "selected").attr("value", supplier.id).text(supplier.description + ' (' + supplier.products_amount + ')'));    
                        } else {
                            suppliersSelect.append($("<option></option>").attr("value", supplier.id).text(supplier.description + ' (' + supplier.products_amount + ')'));
                        }
 
                    }
                }
                function renderCategoriesList(categories){
                    var categoriesList = $('#supplierCategoriesList');
                    var exportProductsButton = $('#exportProducts');
                    var count = categories.length;
                                    
                    categoriesList.empty();

                    for (var i = 0; i < count; i++) {
                        categoriesList.append(renderCategory(categories[i], 0));
                    }
                    if (count === 0) {
                        categoriesList.append('<span>У выбранного поставщика нет товаров</span>');
                    } else {
                        categoriesList.children().each(function() {
                            $(this).addClass('checkboxTree');
                        });
                        $(".checkboxTree").tree({ });
                        exportProductsButton.show();

                        if (!categoriesList.find("ul:not(:has(ul)) :checked").length) {
                            exportProductsButton.attr("disabled", "true");
                        }
                    }

                    categoriesList.show();
                }
                function renderCategory(category){
                    var html = '';
                    var childrenCount = category.children.length;
                    var checked = category.checked ? 'checked="checked"' : null 
                    
                    html += '<ul><li><label><input type="checkbox" ' + checked +' value="' + category.id + '">' + category.data.title;

                    for (var i = 0; i < childrenCount; i++) {
                        html += renderCategory(category.children[i]);
                    }

                    html += '</label></li></ul>';

                    return html;
                }

                $('#suppliersList').mouseup(function(){
                    var obj = $(this);

                    if (obj.children().size() > 0) {
                        $.ajax({
                            url: "<?= html_entity_decode($urls['getSupplierCategoriesList']) ?>",
                            type: 'POST',
                            data: $.param({supplier: obj.val()}),
                            dataType: 'json',
                            success: function(response) {
                                var categories = response.status ? response.data : [{id: null, data: {title: 'Нет категорий'}, children: []}];

                                renderCategoriesList(categories);
                            }
                        });
                    }
                });
                
                $("select[name=relate_delivery]").bind("click", function(e) {
    				lastValue = $(this).val();
    			}).bind("change",function(e){
					if(confirm('Вы хотите изменить привязку доставки?')) {
						var oc_delivery_code = $(this).get(0).id;
						var delivery_id  = $(this).val();
                        $.ajax({
                            url: "<?=html_entity_decode($urls['changeRelateDelivery'])?>",
                            type: 'POST',
                            data: $.param({"delivery_id": delivery_id, "oc_delivery_code": oc_delivery_code}),
                            dataType: 'json',
                            success: function(response) {}
                        });
					} else {
						$(this).val(lastValue);
						return false
					}
                });
                
                $("#suppliersList").bind("click", function(e) {
    				lastValue = $(this).val();
    			}).bind("change",function(e){
                    if(!confirm('Внимание! При смене поставщика будут потеряны все данные')) {
                    	$(this).val(lastValue);
                        return false;
                    }     				
    			});
 
                
                $("#supplierCategoriesList").click(function() {
                    var categoriesList = $('#supplierCategoriesList');
                    var exportProductsButton = $('#exportProducts');

                    if (categoriesList.find("ul:not(:has(ul)) :checked").length) {
                        exportProductsButton.removeAttr("disabled");
                    } else {
                        exportProductsButton.attr("disabled", "true");
                    }
                });
                $('#exportProducts').click(function() {
                	$('#exportProducts').attr("disabled", "true");
                	$('#exportProducts_status').text('Загрузка товаров');
                	
                    var checkedCategories = $('#supplierCategoriesList').find("ul:not(:has(ul)) :checked");
                    var supplier = $('#suppliersList').val();
                    
                    var loadProduct = function(arCategory) {
                        $.ajax({
                            url: "<?= html_entity_decode($urls['loadProductsByCategory']) ?>",
                            type: 'POST',
                            data: $.param({"category[]": arCategory, supplier: supplier}),
                            dataType: 'json',
                            success: function(response) {
                                if(response.status == 0) {
                                	$('#exportProducts').removeAttr("disabled");
                                	$('#exportProducts_status').text('');
									alert('Товары загружены');
                                }
                            }
                        });
                    };

                    var arCategory = [];
                    checkedCategories.each(function() {
                        arCategory.push($(this).val());
                    });
                    
                    loadProduct(arCategory);
                });

                // TODO All strings to $trans.
                $(document).ready(function(){
                    var testConnectionButton = $('button#testConnection');
                    var getSuppliersListButton = $('button#getSuppliersList');
                    var getSupplierProductsListButton = $('button#getsupplierProductsList');

                    
                    testConnectionButton.click(function(){
                        var login = $('input#login').val();
                        var password = $('input#password').val();
                        var hash = $('input#hash').val();
                        var form = $('form#form');

                        hideFormNotifies();
                        if (!login || !password || !hash) {
                            setNotify('Заполните все поля');
                            showFormNotifies();
                        } else {
                            $.ajax({
                                url: "<?= html_entity_decode($urls['checkAuth']) ?>",
                                type: 'POST',
                                data: form.serialize(),
                                dataType: 'json',
                                success: function(response) {
                                    if (!response) {
                                        return;
                                    }

                                    if (response.status) {
                                        setNotify('Успешно');
                                        showFormNotifies();
                                    } else {
                                        setNotify('Ошибка введенных данных');
                                        showFormNotifies();
                                    }
                                },
                                error: function() {
                                    setNotify('Ошибка сервера');
                                    showFormNotifies();
                                }
                            });
                        }
                    });

                    getSuppliersListButton.click(function(){
                        $.ajax({
                            url: "<?= html_entity_decode($urls['getSuppliersList']) ?>",
                            dataType: 'json',
                            success: function(response) {
                                var suppliers = response.status ? response.data : [{id: null, description: 'Поставщики отсутствуют', products_amount: ''}];

                                renderSuppliersList(suppliers);
                                getSupplierProductsListButton.show();
                            }
                        });
                    });
                    getSuppliersListButton.click();
                    
                    getSupplierProductsListButton.click(function() {
                    	getSupplierProductsListButton.attr('disabled','disabled');
                    	
                        var $suppliers = $('#suppliersList');
                        var selectedValue = $suppliers.find(':selected').val();

                        if (selectedValue) {
                            $.ajax({
                                url: "<?= html_entity_decode($urls['getSupplierCategoriesList']) ?>",
                                type: 'POST',
                                data: $.param({supplier: selectedValue}),
                                dataType: 'json',
                                success: function(response) {
                                    if (!response.status) {
                                        $('#supplierCategoriesList').append('<span>Ошибка ...</span>');
                                    } else { 
                                        renderCategoriesList(response.data);
                                    }
                                    
                                    getSupplierProductsListButton.removeAttr('disabled');
                                }
                            });
                        }
                    });
                });
            </script>
        </div>
    </div>
</div>

<?= $footer; ?>
