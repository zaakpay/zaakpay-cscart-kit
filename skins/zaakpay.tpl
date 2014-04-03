<div class="form-field">
	<label for="merchant_id">{$lang.merchant_id}:</label>
	<input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}" class="input-text" />
</div>

<div class="form-field">
	<label for="secret_key">{$lang.secret_key}:</label>
	<input type="text" name="payment_data[processor_params][secret_key]" id="secret_key" value="{$processor_params.secret_key}" class="input-text" />
</div>

<div class="form-field">
	<label for="transaction_mode">{$lang.transaction_mode}:</label>
	<select name="payment_data[processor_params][transaction_mode]" id="transaction_mode">
		<option value="test" {if $processor_params.transaction_mode == "test"}selected="selected"{/if}>{$lang.test}</option>
		<option value="live" {if $processor_params.transaction_mode == "live"}selected="selected"{/if}>{$lang.live}</option>
	</select>
</div>

<div class="form-field">
	<label for="ip_address">{$lang.ip_address}:</label>
	<input type="text" name="payment_data[processor_params][ip_address]" id="ip_address" value="{$processor_params.ip_address}" class="input-text" />
</div>


<div class="form-field">
	<label for="order_prefix">{$lang.order_prefix}:</label>
	<input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}" class="input-text" />
</div>

<div class="form-field">
	<label for="log_params">{$lang.log_params}:</label>
	<select name="payment_data[processor_params][log_params]" id="log_params">
		<option value="yes" {if $processor_params.log_params == "yes"}selected="selected"{/if}>{$lang.yes}</option>
		<option value="no" {if $processor_params.log_params == "no"}selected="selected"{/if}>{$lang.no}</option>
	</select>
</div>