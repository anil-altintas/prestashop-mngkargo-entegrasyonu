{*
* MNG Kargo Sipariş Detay Şablonu
*}

<div class="box">
    <h2>{l s='MNG Kargo Bilgileri' mod='mngkargo'}</h2>
    
    {if isset($tracking_number) && $tracking_number}
        <p>
            <strong>{l s='Takip Numarası:' mod='mngkargo'}</strong>
            <a href="https://www.mngkargo.com.tr/gonderitakip/{$tracking_number|escape:'html':'UTF-8'}" target="_blank">
                {$tracking_number|escape:'html':'UTF-8'}
            </a>
        </p>
        
        {if isset($shipping_status) && $shipping_status}
            <p>
                <strong>{l s='Kargo Durumu:' mod='mngkargo'}</strong>
                {$shipping_status|escape:'html':'UTF-8'}
            </p>
        {/if}
    {else}
        <p class="alert alert-info">
            {l s='Bu sipariş için henüz kargo bilgisi bulunmamaktadır.' mod='mngkargo'}
        </p>
    {/if}
</div> 