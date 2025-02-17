{*
* MNG Kargo Gönderi Detay Şablonu
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-truck"></i> {l s='Gönderi Detayları' mod='mngkargo'}
    </div>
    
    {if isset($shipment) && $shipment}
        <div class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Gönderi ID:' mod='mngkargo'}</label>
                <div class="col-lg-9">
                    <p class="form-control-static">{$shipment->id_shipment|escape:'html':'UTF-8'}</p>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Sipariş ID:' mod='mngkargo'}</label>
                <div class="col-lg-9">
                    <p class="form-control-static">
                        <a href="{$link->getAdminLink('AdminOrders')|escape:'html':'UTF-8'}&id_order={$shipment->id_order|intval}&vieworder">
                            {$shipment->id_order|escape:'html':'UTF-8'}
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Takip Numarası:' mod='mngkargo'}</label>
                <div class="col-lg-9">
                    <p class="form-control-static">{$shipment->tracking_number|escape:'html':'UTF-8'}</p>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Durum:' mod='mngkargo'}</label>
                <div class="col-lg-9">
                    <p class="form-control-static">{$shipment->shipping_status|escape:'html':'UTF-8'}</p>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Oluşturulma Tarihi:' mod='mngkargo'}</label>
                <div class="col-lg-9">
                    <p class="form-control-static">{$shipment->created_at|escape:'html':'UTF-8'}</p>
                </div>
            </div>
            
            {if isset($shipmentStatus) && $shipmentStatus}
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Güncel Durum:' mod='mngkargo'}</label>
                    <div class="col-lg-9">
                        <p class="form-control-static">
                            {$shipmentStatus.status|escape:'html':'UTF-8'} - 
                            {$shipmentStatus.description|escape:'html':'UTF-8'}
                        </p>
                    </div>
                </div>
                
                {if isset($shipmentStatus.lastUpdate)}
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Son Güncelleme:' mod='mngkargo'}</label>
                        <div class="col-lg-9">
                            <p class="form-control-static">{$shipmentStatus.lastUpdate|escape:'html':'UTF-8'}</p>
                        </div>
                    </div>
                {/if}
            {/if}
            
            {if $shipment->label_url}
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Kargo Etiketi:' mod='mngkargo'}</label>
                    <div class="col-lg-9">
                        <a href="{$shipment->label_url|escape:'html':'UTF-8'}" target="_blank" class="btn btn-default">
                            <i class="icon-download"></i> {l s='Etiketi İndir' mod='mngkargo'}
                        </a>
                    </div>
                </div>
            {/if}
        </div>
    {else}
        <div class="alert alert-warning">
            {l s='Gönderi bulunamadı.' mod='mngkargo'}
        </div>
    {/if}
    
    <div class="panel-footer">
        <a href="{$link->getAdminLink('AdminMNGKargo')|escape:'html':'UTF-8'}" class="btn btn-default">
            <i class="process-icon-back"></i> {l s='Geri' mod='mngkargo'}
        </a>
        
        {if isset($shipment) && $shipment}
            <a href="#" class="btn btn-default pull-right" onclick="window.print(); return false;">
                <i class="process-icon-print"></i> {l s='Yazdır' mod='mngkargo'}
            </a>
        {/if}
    </div>
</div> 