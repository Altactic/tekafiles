<div class='wrap'>

	<?php if(isset($file)): ?>
	<h2>Editar <?php echo $file->title; ?></h2>
	<?php else: ?>
	<h2>Nuevo Documento</h2>
	<?php endif; ?>

	<a href='<?php echo admin_url("admin.php?page=tekafiles.php"); ?>'>Volver a la lista de archivos</a>

	<form id="tekafile"
		method='post'
		action='<?php echo admin_url( '/admin.php?page=tekafiles_new.php&noheader=true' ); ?>'
		<?php if(!isset($file)) echo "enctype='multipart/form-data'"; ?> >

		<?php if(isset($_GET['e'])): ?>
		<input type="hidden" name="edit" value="<?php echo $_GET['e'] ?>" />
		<?php endif; ?>

		<?php wp_nonce_field('tekafiles_new_file_nonce'); ?>

		<table class='form-table'>
            
            <?php if(isset($file->title)): ?>
			<tr>
				<th scope='row'>
					<label for='title'>Título</label>
				</th>
				<td>
					<input id='title'
						type='text'
						name='title'
						value='<?php echo $file->title ?>'
						required />
				</td>
			</tr>
            <?php endif; ?>

			
			<?php if(!isset($file)): ?>
			<tr>
				<th scope='row'>
					<label for='file'>Archivo</label>
				</th>
				<td>
					<input id='file'
						type='file'
						name='files[]'
						title='Buscar'
                        multiple="multiple"
						required />
				</td>
			</tr>
			<?php endif; ?>

			<tr>
				<th scope='row'>
					<label for='category'>Categoría</label>
				</th>
				<td>
					<input id='category'
						type='text'
						name='category'
						list='category-datalist'
						autocomplete='off'
						value='<?php echo $file->category ?>' />
					<datalist id='category-datalist' ></datalist>
				</td>
			</tr>

			<tr>
				<th scope='row'>
					<label for='enabled'>Habilitado</label>
				</th>
				<td>
					<input id='enabled'
						type='checkbox'
						name='enabled'
						<?php if ($file->enabled): ?>checked<?php endif; ?> />
				</td>
			</tr>

		</table>
		<div>
			<input type='submit' value='Enviar' class='button button-primary' name='submit' id='submit' />
		</div>
	</form>
</div>
