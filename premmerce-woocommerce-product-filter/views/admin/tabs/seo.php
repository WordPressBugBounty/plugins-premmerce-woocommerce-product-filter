<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$generateUrl = '';
$updateUrl = '';
$disabled = 'disabled';
$getPage = ( isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '' );
$getTab = ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '' );
/**
 * Seo rules info
 *
 * @var \Premmerce\Filter\Seo\RulesTable $rulesTable
*/
?>
<div class="pf-wrap-flex">

<div id="" class="pf-wrap-flex__col">

	<a type="button" class="button" href="<?php 
echo  esc_url( $generateUrl ) ;
?>" <?php 
echo  esc_attr( $disabled ) ;
?>>
		<?php 
esc_html_e( 'Generate Rules', 'premmerce-filter' );
?>
	</a>
	<a type="button" class="button" href="<?php 
echo  esc_url( $updateUrl ) ;
?>" <?php 
echo  esc_attr( $disabled ) ;
?>>
		<?php 
esc_html_e( 'Update paths', 'premmerce-filter' );
?>
	</a>

	<div class="col-wrap">
		<?php 
require __DIR__ . '/../seo/form.php';
?>
	</div>
</div>

<div class="pf-wrap-flex__col">
	<div class="col-wrap pf-rules-table">
		<form method="GET" class="search-form wp-clearfix">
			<?php 
$rulesTable->search_box( __( 'Search', 'premmerce-filter' ), 'search' );
?>
			<input type="hidden" name="page" value="<?php 
echo  esc_attr( $getPage ) ;
?>">
			<input type="hidden" name="tab" value="<?php 
echo  esc_attr( $getTab ) ;
?>">
		</form>
		<form method="GET">
			<input type="hidden" name="page" value="<?php 
echo  esc_attr( $getPage ) ;
?>">
			<input type="hidden" name="tab" value="<?php 
echo  esc_attr( $getTab ) ;
?>">
			<?php 
$rulesTable->display();
?>
		</form>
	</div>
</div>
</div>
