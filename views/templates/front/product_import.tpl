{*
 * 2010-2018 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 *}

<!-- Sender product json -->
	<script type="application/sender+json">
	{ldelim} 
		"name" : "{$product['name']|escape:'htmlall':'UTF-8'}",
		"image" : "{$product['image']|escape:'htmlall':'UTF-8'}",
        "description" : "{$product['description']|escape:'htmlall':'UTF-8'}",
        "price" : "{$product['price']|escape:'htmlall':'UTF-8'}",
        "special_price" : "{$product['special_price']|escape:'htmlall':'UTF-8'}",
        "currency" : "{$product['currency']|escape:'htmlall':'UTF-8'}",
        "quantity" : "{$product['quantity']|escape:'htmlall':'UTF-8'}",
        "discount" : "{$product['discount']|escape:'htmlall':'UTF-8'}"
	{rdelim}
	</script>
<!-- Sender product json: end -->
