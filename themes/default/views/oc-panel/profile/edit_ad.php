<?php defined('SYSPATH') or die('No direct script access.');?>

<?=Form::errors()?>

<div class="page-header">
    <a class="btn btn-primary pull-right" target="_blank" href="<?=Route::url('ad', array('controller'=>'ad','category'=>$ad->category->seoname,'seotitle'=>$ad->seotitle))?>">
        <?=__('View Advertisement')?>
    </a>
    <h1><?=$ad->title?> <small><?=__('Edit Advertisement')?></small></h1>
    <?$str=NULL;switch ($ad->status) {
        case Model_Ad::STATUS_NOPUBLISHED:
            $str = __('NOPUBLISHED');
            break;
        case Model_Ad::STATUS_PUBLISHED:
            $str = __('PUBLISHED');
            break;
        case Model_Ad::STATUS_UNCONFIRMED:
            $str = __('UNCONFIRMED');
            break;
        case Model_Ad::STATUS_SPAM:
            $str = __('SPAM');
            break;
        case Model_Ad::STATUS_UNAVAILABLE:
            $str = __('UNAVAILABLE');
            break;
        default:
            break;
    }?>
    <p><span class="label label-warning label-as-badge"><?=$str?></span></p>

    <? if( $ad->status == Model_Ad::STATUS_UNAVAILABLE AND !in_array(core::config('general.moderation'), Model_Ad::$moderation_status)  
            ):?>
            <a
                href="<?=Route::url('oc-panel', array('controller'=>'myads','action'=>'activate','id'=>$ad->id_ad))?>" 
                class="btn btn-success" 
                title="<?=__('Activate?')?>" 
                data-toggle="confirmation" 
                data-btnOkLabel="<?=__('Yes, definitely!')?>" 
                data-btnCancelLabel="<?=__('No way!')?>">
                <i class="glyphicon glyphicon-ok"></i> <?=__('Activate')?>
            </a>
    <?endif?>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">	
                <?foreach ($orders as $order):?>
                    <a class="btn btn-warning" href="<?=Route::url('default', array('controller'=> 'ad','action'=>'checkout' , 'id' => $order->id_order))?>">
                    <i class="glyphicon glyphicon-shopping-cart"></i> <?=__('Pay')?> <?=$order->description?>  
                    </a>
                <?endforeach?>

                <!-- PAYPAL buttons to featured and to top -->
                <?if((core::config('payment.pay_to_go_on_top') > 0  
                        AND core::config('payment.to_top') != FALSE )
                        OR (core::config('payment.to_featured') != FALSE AND $ad->featured < Date::unix2mysql() )):?>
                    <div id="recomentadion" class="well recomentadion clearfix">
                        <?if(core::config('payment.pay_to_go_on_top') > 0 AND core::config('payment.to_top') != FALSE):?>
                            <p class="text-info"><?=__('Your Advertisement can go on top again! For only ').i18n::format_currency(core::config('payment.pay_to_go_on_top'),core::config('payment.paypal_currency'));?></p>
                            <a class="btn btn-xs btn-primary" type="button" href="<?=Route::url('default', array('action'=>'to_top','controller'=>'ad','id'=>$ad->id_ad))?>"><?=__('Go Top!')?></a>
                        <?endif?>
                        <?if(core::config('payment.to_featured') != FALSE AND $ad->featured < Date::unix2mysql()):?>
                            <p class="text-info"><?=__('Your Advertisement can go to featured! For only ').i18n::format_currency(Model_Order::get_featured_price(),core::config('payment.paypal_currency'));?></p>
                            <a class="btn btn-xs btn-primary" type="button" href="<?=Route::url('default', array('action'=>'to_featured','controller'=>'ad','id'=>$ad->id_ad))?>"><?=__('Go Featured!')?></a>
                        <?endif?>
                    </div>
                <?endif?>
                <!-- end paypal button -->
                <?if(Auth::instance()->get_user()->id_role == Model_Role::ROLE_ADMIN):?>
                    <div class="control">
                        <? $owner = new Model_User($ad->id_user)?>
                        <table class="table table-bordered admin-table-user">
                            <tr>
                                <th><?=__('Id_User')?></th>
                                <th><?=__('Profile')?></th>
                                <th><?=__('Name')?></th>
                                <th><?=__('Email')?></th>
                                <th><?=__('Status')?></th>
                            </tr>
                            <tbody>
                                <tr>
                                    <td><p><?= $ad->id_user?></p></td>
                                    <td>	
                                        <a href="<?=Route::url('profile', array('seoname'=>$owner->seoname))?>" alt=""><?= $owner->seoname?></a>
                                    </td>
                                    <td><p><?= $owner->name?></p></td>
                                    <td>	
                                        <a href="<?=Route::url('contact')?>"><?= $owner->email?></a>
                                    </td>
                                    <td>
                                        <?$str=NULL;switch ($ad->status) {
                                            case Model_Ad::STATUS_NOPUBLISHED:
                                                $str = __('NOPUBLISHED');
                                                break;
                                            case Model_Ad::STATUS_PUBLISHED:
                                                $str = __('PUBLISHED');
                                                break;
                                            case Model_Ad::STATUS_UNCONFIRMED:
                                                $str = __('UNCONFIRMED');
                                                break;
                                            case Model_Ad::STATUS_SPAM:
                                                $str = __('SPAM');
                                                break;
                                            case Model_Ad::STATUS_UNAVAILABLE:
                                                $str = __('UNAVAILABLE');
                                                break;
                                            default:
                                                break;
                                        }?>	
                                        <b><?=$str?></b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?endif?>

                <?= FORM::open(Route::url('oc-panel', array('controller'=>'myads','action'=>'update','id'=>$ad->id_ad)), array('class'=>'form-horizontal edit_ad_form', 'enctype'=>'multipart/form-data'))?>
                    <fieldset>
                    
                        <!-- category select -->
                        <div class="category_edit">
                            <label for="category"><?=__('Selected Category')?>: <label for="category" class="selected-category"><?=$ad->category->name?></label></label>  
                            <br>
                            <a class=" btn btn-default"><?=__('Edit category')?></a>
                            <br><br>
                        </div>
                        <div class="category_chained hide">
                            <label for="category"><span class="pull-left"><?=__('Category')?></span>
                                <span class="label label-warning category-price ml-10"></span>
                                <input value="<?=$ad->id_category?>" class="invisible pull-left" id="category-selected" name="category" style="height: 0; padding:0; width:0;" required ></input>
                            </label>
                            <div class="form-group">
                                <?foreach ($order_parent_deep as $level => $categ):?>
                                    <div class="col-md-4">
                                    <select id="level-<?=$level?>" data-level="<?=$level?>" 
                                            class="disable-chosen category_chained_select <?=(core::config('advertisement.parent_category') AND $level == 0)?'is_parent':NULL?> form-control">
                                        <option value=""></option>
                                        <?foreach ($categ as $c):?>
                                            <?if($c['id']>1):?>
                                            <option data-price="<?=($c['price']>0)?$c['price']:NULL?>" value="<?=$c['id']?>" class="<?=$c['id_category_parent']?>"><?=$c['name']?></option>
                                            <?endif?>
                                        <?endforeach?>
                                    </select>
                                    </div>
                                <?endforeach?>

                                <div class="clearfix"></div>
                                <div class="col-md-4">
                                    <label for="category"><?=__('Selected Category')?>: <label for="category" class="selected-category"><?=$ad->category->name?></label></label>  
                                </div>
                            </div>
                        </div>
                        <div class="location_edit">
                            <label for="location"><?=__('Selected location')?>: <label for="location" class="selected-location"><?=$ad->location->name?></label></label>  
                            <br>
                            <a class=" btn btn-default"><?=__('Edit location')?></a>
                            <br><br>
                        </div>
                        <div class="location_chained hide">
                            <label for="location"><span class="pull-left"><?=__('Location')?></span>
                                <span class="label label-warning ml-10"></span>
                                <input value="<?=$ad->id_location?>" class="invisible pull-left" id="location-selected" name="location" style="height: 0; padding:0; width:0;" required></input>
                            </label>

                            <div class="form-group">
                                <?foreach ($loc_parent_deep as $level => $locat):?>
                                    <div class="col-md-4">
                                    <select id="level-loc-<?=$level?>" data-level="<?=$level?>" 
                                            class="disable-chosen location_chained_select form-control <?=($level != 0)?'hide':NULL?>">
                                        <option value=""></option>
                                        <?foreach ($locat as $l):?>
                                            <?if($l['id']>1):?>
                                            <option value="<?=$l['id']?>" class="<?=$l['id_location_parent']?>"><?=$l['name']?></option>
                                            <?endif?>
                                        <?endforeach?>
                                    </select>
                                    </div>
                                <?endforeach?>

                                <div class="clearfix"></div>
                                <div class="col-md-4">
                                    <label for="location"><?=__('Selected location')?>: <label for="location" class="selected-location"></label></label>  
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-8">
                                <?= FORM::label('title', __('Title'), array('class'=>'', 'for'=>'title'))?>
                                <?= FORM::input('title', $ad->title, array('placeholder' => __('Title'), 'class' => 'form-control', 'id' => 'title', 'required'))?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8">
                                <?= FORM::label('description', __('Description'), array('class'=>'', 'for'=>'description', 'spellcheck'=>TRUE))?>
                                <?= FORM::textarea('description', $ad->description, array('class'=>'form-control col-md-9 col-sm-9 col-xs-12'.((Core::config("advertisement.description_bbcode"))?NULL:' disable-bbcode'), 'name'=>'description', 'id'=>'description', 'rows'=>8, 'required'))?>
                            </div>
                        </div>
                        <?if(core::config('advertisement.phone') != FALSE):?>
                        <div class="form-group">
                            <div class="col-sm-8">
                                <?= FORM::label('phone', __('Phone'), array('class'=>'', 'for'=>'phone'))?>
                                <?= FORM::input('phone', $ad->phone, array('class'=>'form-control', 'id'=>'phone', 'placeholder'=>__('Phone')))?>
                            </div>
                        </div>
                        <?endif?>
                        <?if(core::config('advertisement.address') != FALSE):?>
                        <div class="form-group">
                            <div class="col-sm-8">
                                <?= FORM::label('address', __('Address'), array('class'=>'', 'for'=>'address'))?>
                                <?if(core::config('advertisement.map_pub_new')):?>
                                    <div class="input-group">
                                        <?= FORM::input('address', $ad->address, array('class'=>'form-control', 'id'=>'address', 'placeholder'=>__('Address')))?>
                                        <span class="input-group-btn">
                                            <button class="btn btn-default locateme" type="button"><?=__('Locate me')?></button>
                                        </span>
                                    </div>
                                <?else:?>
                                    <?= FORM::input('address', $ad->address, array('class'=>'form-control', 'id'=>'address', 'placeholder'=>__('Address')))?>
                                <?endif?>
                            </div>
                        </div>
                        <?if(core::config('advertisement.map_pub_new')):?>
                            <div class="popin-map-container">
                                <div class="map-inner" id="map" 
                                    data-lat="<?=($ad->latitude)? $ad->latitude:core::config('advertisement.center_lat')?>" 
                                    data-lon="<?=($ad->longitude)? $ad->longitude:core::config('advertisement.center_lon')?>"
                                    data-zoom="<?=core::config('advertisement.map_zoom')?>" 
                                    style="height:200px;max-width:400px;margin-bottom:5px;">
                                </div>
                            </div>
                            <input type="hidden" name="latitude" id="publish-latitude" value="<?=$ad->latitude?>">
                            <input type="hidden" name="longitude" id="publish-longitude" value="<?=$ad->longitude?>">
                        <?endif?>
                        <?endif?>
                        <?if(core::config('payment.stock')):?>
                        <div class="form-group">
                            <div class="col-sm-8">
                                <?= FORM::label('stock', __('In Stock'), array('class'=>'', 'for'=>'stock'))?>
                                <div class="input-prepend">
                                <?= FORM::input('stock', $ad->stock, array('placeholder' => '10', 'class' => 'form-control', 'id' => 'stock', 'type'=>'text'))?>
                                </div>
                            </div>
                        </div>
                        <?endif?>
                        <?if(core::config('advertisement.website') != FALSE):?>
                        <div class="form-group">
                            <div class="col-sm-8">
                                <?= FORM::label('website', __('Website'), array('class'=>'', 'for'=>'website'))?>
                                <?= FORM::input('website', $ad->website, array('class'=>'form-control', 'id'=>'website', 'placeholder'=>__('Website')))?>
                            </div>
                        </div>
                        <?endif?>
                        <?if(core::config('advertisement.price') != FALSE):?>
                        <div class="form-group">
                            <div class="col-sm-8">
                                <?= FORM::label('price', __('Price'), array('class'=>'', 'for'=>'price'))?>
                                <div class="input-prepend">
                                    <?= FORM::input('price', $ad->price, array('placeholder'=>html_entity_decode(i18n::money_format(1)),'class'=>'form-control', 'id' => 'price'))?>
                                </div>
                            </div>
                        </div>
                        <?endif?>
                        <!-- Fields coming from custom fields feature -->
                        <?if (Theme::get('premium')==1):?>
                            <?if(isset($fields)):?>
                                <?if (is_array($fields)):?>
                                    <?foreach($fields as $name=>$field):?>
                                    <div class="form-group" id="cf_new">
                                    <?$cf_name = 'cf_'.$name?>
                                    <?if($field['type'] == 'select' OR $field['type'] == 'radio') {
                                        $select = array(''=>'');
                                        foreach ($field['values'] as $select_name) {
                                            $select[$select_name] = $select_name;
                                        }
                                    } else $select = $field['values']?>

                                            <?=Form::cf_form_tag('cf_'.$name, array(    
                                                'display'   => $field['type'],
                                                'label'     => $field['label'],
                                                'tooltip'   => (isset($field['tooltip']))? $field['tooltip'] : "",
                                                'default'   => $ad->$cf_name,
                                                'options'	=> (!is_array($field['values']))? $field['values'] : $select,
                                                'required'	=> $field['required'],
                                                'categories'=> (isset($field['categories']))? $field['categories'] : "",))?>

                                    </div>     
                                    <?endforeach?>
                                <?endif?>
                            <?endif?>
                        <?endif?>
                        <!-- /endcustom fields -->
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="row">
                                <?$images = $ad->get_images()?>
                                <?if($images):?>
                                    <?foreach ($images as $key => $value):?>
                                        <?if(isset($value['thumb'])): // only formated images (not originals)?>
                                            <div id="img<?=$key?>" class="col-md-4 col-sm-4 col-md-4 edit-image">
                                                <a class="">
                                                    <img src="<?=$value['thumb']?>" class="img-rounded thumbnail" alt="">
                                                </a>
                                                <button class="btn btn-danger index-delete img-delete"
                                                        data-title="<?=__('Are you sure you want to delete?')?>" 
                                                        data-btnOkLabel="<?=__('Yes, definitely!')?>" 
                                                        data-btnCancelLabel="<?=__('No way!')?>"
                                                        type="submit" 
                                                        name="img_delete"
                                                        value="<?=$key?>" 
                                                        href="<?=Route::url('oc-panel', array('controller'=>'myads','action'=>'update','id'=>$ad->id_ad))?>" 
                                                        title="<?=__('Delete image')?>">
                                                        <?=__('Delete')?>
                                                </button>
                                            </div>
                                        <?endif?>
                                    <?endforeach?>
                                <?endif?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <?if (core::config('advertisement.num_images') > count($images)):?> <!-- permition to add more images-->
                                <div class="col-sm-8">
                                    <?= FORM::label('images', __('Images'), array('class'=>'', 'for'=>'images0'))?>
                                    <input type="file" name='image0' id='fileInput0' />
                                </div>
                            <?endif?>
                        </div>
                        <div class="page-header"></div>
                            <?= FORM::button('submit', (in_array(core::config('general.moderation'), Model_Ad::$moderation_status))?__('Publish'):__('Update'), array('type'=>'submit', 'class'=>'btn btn-primary', 'action'=>Route::url('oc-panel', array('controller'=>'myads','action'=>'update','id'=>$ad->id_ad))))?>

                    </fieldset>
                <?= FORM::close()?>
            </div>
        </div>		
    </div>
</div>
<div class="modal modal-statc fade" id="processing-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-body">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?=__('Processing...')?></h4>
                </div>
                <div class="modal-body">
                    <div class="progress progress-striped active">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>