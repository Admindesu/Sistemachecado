/**
 * Envía por POST los parámetros de una URL (p. ej. "empleado.php?id=5") junto con
 * el token CSRF de <meta name="csrf-token">. Se usa para eliminar registros:
 * por GET cualquier enlace externo podría borrar datos.
 */
function enviarPost(url) {
	var destino = new URL(url, window.location.href);
	var form = document.createElement('form');
	form.method = 'POST';
	form.action = destino.pathname;
	var agregar = function (nombre, valor) {
		var input = document.createElement('input');
		input.type = 'hidden';
		input.name = nombre;
		input.value = valor;
		form.appendChild(input);
	};
	destino.searchParams.forEach(function (valor, nombre) { agregar(nombre, valor); });
	var meta = document.querySelector('meta[name="csrf-token"]');
	if (meta) agregar('csrf', meta.getAttribute('content'));
	document.body.appendChild(form);
	form.submit();
}

function advertencia(e) {
	e.preventDefault();	/* para que no se ejecute el evento por defecto */
	var url= e.currentTarget.getAttribute('href');	/* para obtener el atributo href del evento */
	Swal.fire({
		title: '¿Está seguro?',
		text: '¡ No podrá recuperar este registro !',
		icon: 'warning',	/*icono que va mostrar success-error-info-warning-question*/
		showCancelButton: true,
		confirmButtonColor: '#2CB073',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Si, Eliminar',
		cancelButtonText: 'No, Salir',
		reverseButtons: true,
		//width:'300px',
		padding: '20px',
		//background: 'rgb(70,200,255)',
		backdrop: true,	/* color oscuro de la pagina true-false */

		position: 'top',	/* posicion de ubicacion top--bottom--center top-end bottom-end top-start */
		/* bottom-start center-start center-end */

		allowOutsideClick: true,	/* para NO salir con un click */
		allowEscapeKey: true,	/* para SI salir con un escape */
		allowEnterKey: false,	/* para SI salir con un enter */

	}).then((result) => {
		/* Read more about isConfirmed, isDenied below */
		if (result.isConfirmed) {
		 // this.submit();
		 enviarPost(url);	/* elimina por POST con token CSRF */
		}
	  })

}