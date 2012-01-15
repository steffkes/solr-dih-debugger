<?php

function filter_by_prefix( array $array, $prefix = null )
{
	$allowed_keys = array_filter
	(
		array_keys( $array ),
		function( $entity ) use( $prefix )
		{
			if( is_null( $prefix ) )
			{
				return 0 === substr_count( $entity, SEP );
			}

			return substr_count( $prefix, SEP ) + 1 === substr_count( $entity, SEP ) && 
					0 === strpos( $entity, $prefix.SEP );
		}
	);

	return array_intersect_key
	(
		$array,
		array_flip( $allowed_keys )
	);
}

function get_query( $entity, $run = null )
{
	global $data_config;
	return $data_config->get_document()->get_query( $entity, $run );
};

function compute_row_markup( array $row, $run, $prefix = null )
{
	$filtered_row = filter_by_prefix( $row, $prefix );

	if( 0 === count( $filtered_row ) )
	{
		return null;
	}

	$markup = '';

	foreach( $filtered_row as $entity => $fields )
	{
		$markup .= '<li>

			<strong>'.end( explode( SEP, $entity ) )."\n";

				$query = get_query( $entity, $run );
				if( $query )
				{
					$markup .= '<span class="query" title="'.htmlspecialchars( $query ).'">?</span>'."\n";
				}

			$markup .= '</strong>'."\n";

			// --

			$markup .= '<ul class="fields">'."\n";

				foreach( $fields as $name => $data )
				{
					$markup .= '<li rel="'.$entity.'~'.$name.'">
						<ul>'."\n";

						foreach( $data as $value )
						{
							if( is_null( $value ) )
							{
								$markup .= '<li class="empty">∅</li>'."\n";
							}
							else
							{
								$markup .= '<li>'.$value.'</li>'."\n";
							}
						}
						
						$markup .= '</ul>
					</li>'."\n";
				}
			
			$markup .= '</ul>'."\n";

			// --

			$function = __FUNCTION__;
			$sub_entity = $function( $row, $run, $entity );
			if( $sub_entity )
			{
				$markup .= '<ul class="sub">'."\n";
				$markup .= $sub_entity."\n";
				$markup .= '</ul>'."\n";
			}
		
		$markup .= '</li>'."\n";
	}

	return $markup;
}

function compute_meta_markup( array $meta, $prefix = null )
{
	$filtered_meta = filter_by_prefix( $meta, $prefix );

	if( 0 === count( $filtered_meta ) )
	{
		return null;
	}

	$markup = '';
	
	foreach( $filtered_meta as $entity => $data )
	{
		$markup .= '<li class="entity">

			<a>'.end( explode( SEP, $entity ) )."\n";

				$query = get_query( $entity );
				if( $query )
				{
					$markup .= '<span class="query" title="'.htmlspecialchars( $query ).'">?</span>'."\n";
				}

			$markup .= '</a>'."\n";

			// --

			$markup .= '<ul class="fields">'."\n";
				foreach( $data as $field => $x )
				{
					$markup .= '<li rel="'.$entity.'~'.$field.'">'.$field.'</li>'."\n";
				}
			$markup .= '</ul>'."\n";

			// --

			$function = __FUNCTION__;
			$sub_entity = $function( $meta, $entity );
			if( $sub_entity )
			{
				$markup .= '<ul class="sub">'."\n";
				$markup .= $sub_entity."\n";
				$markup .= '</ul>'."\n";
			}
		
		$markup .= '</li>'."\n";
	}

	return $markup;
}

include __DIR__.'/_header.php';

?>

	<h1>products</h1>

	<div id="wrapper" class="clearfix">

		<?php

		$entities = $data_config->get_document()->get_root_entities();
		foreach( $entities as $entity )
		{
			?>
			<div class="root clearfix">

				<div class="results">

					<ul class="clearfix">

						<?php

						$rows = $data_config->get_document()->get_rows_for( $entity );
						foreach( $rows as $run => $row )
						{
							?>
							<li class="row">

								<a><?php echo $run; ?></a>

								<ul>
									<?php echo compute_row_markup( $row, $run ); ?>
								</ul>

							</li>
							<?php
						}

						?>

					</ul>

				</div>

				<div class="meta">

					<ul>

						<?php echo compute_meta_markup( $data_config->get_document()->get_meta_for( $entity ) ); ?>

					</ul>

				</div>
			
			</div>
			<?php
		}

		?>

	</div>

	<script type="text/javascript" src="js/0_console.js"></script>
	<script type="text/javascript" src="js/1_jquery.js"></script>
	<script type="text/javascript" src="js/script.js"></script>

</body>
</html>