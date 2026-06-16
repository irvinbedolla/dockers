@extends('layouts.app')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h3 class="card-title mb-0">Búsqueda de incidencias</h3>
				</div>
				<div class="card-body">
					@if (session('status'))
						<div class="alert alert-success">{{ session('status') }}</div>
					@endif

					<form method="GET" action="{{ route('incidencias.busqueda.index') }}" class="mb-3">
						<div class="row g-2">
							<div class="col-12 col-md-3">
								<select name="tipo" class="form-control">
									@php($tipoSel = old('tipo', $tipo ?? ''))
									@php($hasTipo = !empty($tipoSel))
									@php($hasQuery = !empty($q))
									@php($hasResults = !empty($resultados) && $resultados->isNotEmpty())
									<option value="">Seleccione tipo…</option>
									<option value="SOLICITUD" {{ $tipoSel === 'SOLICITUD' ? 'selected' : '' }}>Solicitud</option>
									<option value="RATIFICACION" {{ $tipoSel === 'RATIFICACION' ? 'selected' : '' }}>Ratificación</option>
								</select>
							</div>
							<div class="col-12 col-md-7" id="q-wrapper" style="display:none;">
								<input
									type="text"
									name="q"
									id="q"
									class="form-control"
									placeholder=""
									value="{{ old('q', $q ?? '') }}"
									autocomplete="off"
								>
							</div>
							<div class="col-12 col-md-2 d-grid" id="btn-wrapper" style="display:none;">
								<button class="btn btn-primary" type="submit">Buscar</button>
							</div>
						</div>
						<small class="text-muted" id="help-text" style="display:none;"></small>
					</form>

					<div class="table-responsive">
						<table class="table table-striped align-middle">
							<thead>
								<tr>
										<th id="th-nue">Núm. Expediente (NUE)</th>
									<th id="th-col2">Solicitante</th>
									<th id="th-col3">Citados</th>
									<th id="th-estatus">Estatus</th>
									<th id="th-motivo">Motivo</th>
									<th style="width: 220px;">Acciones</th>
								</tr>
							</thead>
							<tbody>
								@if (!empty($q) && (empty($resultados) || $resultados->isEmpty()))
									<tr>
										<td colspan="6" class="text-center text-muted">Sin coincidencias para "{{ $q }}"</td>
									</tr>
								@elseif(!empty($resultados) && $resultados->isNotEmpty())
									@foreach($resultados as $resultado)
										<tr>
											@php($esSuperUsuario = auth()->check() && auth()->user()->hasRole('Super Usuario'))
											@if(($resultado->tipo ?? '') === 'RATIFICACION')
												<td>{{ $resultado->NUE }}</td>
												<td>{{ $resultado->empresa }}</td>
												<td>{{ $resultado->trabajador }}</td>
												<td>{{ $resultado->estatus }}</td>
											@else
												<td>{{ $resultado->NUE }}</td>
												<td>{{ $resultado->solicitante }}</td>
												<td>{{ $resultado->citados }}</td>
												<td>{{ $resultado->estatus }}</td>
											@endif
											<td>
												@if($esSuperUsuario && !empty($resultado->incidencia))
													<span class="text-muted">{{ $resultado->motivo_incidencia ?? '—' }}</span>
												@else
													<span class="text-muted">—</span>
												@endif
											</td>
											<td>
												@if($esSuperUsuario && !empty($resultado->incidencia))
													<form method="POST" action="{{ route('incidencias.busqueda.desmarcar') }}" class="d-inline" data-desincidencia-form>
														@csrf
														<input type="hidden" name="id_solicitud" value="{{ $resultado->id }}">
														<input type="hidden" name="tipo" value="{{ $resultado->tipo ?? ($tipo ?? '') }}">
														<input type="hidden" name="q" value="{{ $q }}">
														<button type="submit" class="btn btn-success btn-sm">Desmarcar</button>
													</form>
												@else
													<form method="POST" action="{{ route('incidencias.busqueda.marcar') }}" class="d-inline" data-incidencia-form>
														@csrf
														<input type="hidden" name="id_solicitud" value="{{ $resultado->id }}">
														<input type="hidden" name="tipo" value="{{ $resultado->tipo ?? ($tipo ?? '') }}">
														<input type="hidden" name="q" value="{{ $q }}">
														<button type="submit" class="btn btn-danger btn-sm">Marcar como incidencia</button>
													</form>
												@endif
											</td>
										</tr>
									@endforeach
								@else
									<tr>
										<td colspan="6" class="text-center text-muted">Selecciona un tipo de búsqueda para comenzar.</td>
									</tr>
								@endif
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


@endsection


@section('scripts')
	<style>
		/*Estilos de SweetAlert*/
		.sweet-overlay {
			position: fixed !important;
			z-index: 20000 !important;
		}
		.sweet-alert {
			position: fixed !important;
			top: 50% !important;
			left: 50% !important;
			transform: translate(-50%, -50%) !important;
			margin: 0 !important;
			z-index: 20001 !important;
		}
		/* Asegura que el error del input (SweetAlert v1) se vea rojo aunque el tema lo sobreescriba */
		.sweet-alert .sa-error-container.show {
			background-color: #f8d7da !important;
			color: #842029 !important;
		}
		.sweet-alert .sa-error-container.show p {
			color: #842029 !important;
		}
	</style>

	<script>
		//Ocultar elementos hasta elegir tipo y cambiar placeholder
		document.addEventListener('DOMContentLoaded', function () {
			var tipoSel = document.querySelector('select[name="tipo"]');
			var qWrapper = document.getElementById('q-wrapper');
			var btnWrapper = document.getElementById('btn-wrapper');
			var helpText = document.getElementById('help-text');
			var qInput = document.getElementById('q');
			var thCol2 = document.getElementById('th-col2');
			var thCol3 = document.getElementById('th-col3');
			var resultsTbody = document.querySelector('.table-responsive table tbody');

			function applyTipoUI(tipo) {
				var enabled = !!tipo;
				qWrapper.style.display = enabled ? '' : 'none';
				btnWrapper.style.display = enabled ? '' : 'none';
				helpText.style.display = enabled ? '' : 'none';

				if (!enabled) {
					if (qInput) qInput.placeholder = '';
					thCol2.textContent = 'Solicitante';
					thCol3.textContent = 'Citados';
					helpText.textContent = '';
					return;
				}

				if (tipo === 'RATIFICACION') {
					if (qInput) qInput.placeholder = 'Buscar por NUE, trabajador o empresa';
					thCol2.textContent = 'Empresa';
					thCol3.textContent = 'Trabajador';
					helpText.innerHTML = 'Búsqueda de <strong>Ratificación</strong>: por <strong>NUE</strong>, <strong>trabajador</strong> o <strong>empresa</strong>.';
				} else {
					if (qInput) qInput.placeholder = 'Buscar por NUE o solicitante';
					thCol2.textContent = 'Solicitante';
					thCol3.textContent = 'Citados';
					helpText.innerHTML = 'Búsqueda de <strong>Solicitud</strong>: por <strong>NUE</strong> o <strong>solicitante</strong>.';
				}
			}

			function clearResultsTable() {
				if (!resultsTbody) return;
				resultsTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Selecciona un tipo de búsqueda para comenzar.</td></tr>';
			}

			if (tipoSel) {
				applyTipoUI(tipoSel.value);
				tipoSel.addEventListener('change', function () {
					clearResultsTable();
					if (qInput) qInput.value = '';
					applyTipoUI(this.value);
				});
			}
		});
	</script>

	<script>
		function ensureSwalCloseButton() {
			//X para cerrar la sweet alert (no existe en Sweetalert v1)
			try {
				var modal = document.querySelector('.sweet-alert');
				if (!modal) return;
				if (modal.querySelector('.swal-close')) return;

				modal.style.position = modal.style.position || 'relative';

				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'swal-close';
				btn.setAttribute('aria-label', 'Cerrar');
				btn.innerHTML = '&times;';
				btn.style.position = 'absolute';
				btn.style.top = '8px';
				btn.style.right = '10px';
				btn.style.border = 'none';
				btn.style.background = 'transparent';
				btn.style.fontSize = '26px';
				btn.style.lineHeight = '1';
				btn.style.cursor = 'pointer';
				btn.style.color = '#6c757d';
				btn.style.padding = '0';
				btn.style.margin = '0';
				btn.style.zIndex = '9999';
				btn.onmouseenter = function () { btn.style.color = '#000'; };
				btn.onmouseleave = function () { btn.style.color = '#6c757d'; };
				btn.addEventListener('click', function () {
					try {
						if (window.swal && typeof window.swal.close === 'function') {
							window.swal.close();
							return;
						}
						//Simula click en la X
						var overlay = document.querySelector('.sweet-overlay');
						if (overlay) overlay.click();
					} catch (e) {
						console.error('No se pudo cerrar el SweetAlert:', e);
					}
				});

				modal.appendChild(btn);
			} catch (e) {
				console.error('Error inyectando la X de SweetAlert:', e);
			}
		}

		// Nota: este proyecto usa SweetAlert v1 (sweetalert.min.js) que YA trae input nativo.

		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('[data-incidencia-form]').forEach(function (form) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();

					const titulo = 'Marcar como incidencia';
						const texto = 'Ingresa el motivo del marcaje:';

					//SweetAlert v1
					if (typeof window.swal === 'function') {
						window.swal({
							title: titulo,
								text: texto,
							type: 'warning',
								inputType: 'text',
								inputPlaceholder: 'Motivo (requerido)',
								closeOnConfirm: false,
							//Permitir cerrar con ESC o click fuera
							allowEscapeKey: true,
							allowOutsideClick: true,
							showCancelButton: true,
							confirmButtonText: 'Confirmar',
							cancelButtonText: 'Cancelar',
							confirmButtonColor: '#dc3545'
							}, function (inputValue) {
							//Inyectar X al mostrar
							setTimeout(ensureSwalCloseButton, 0);
								setTimeout(function () {
									var modal = document.querySelector('.sweet-alert');
									if (modal) modal.classList.add('show-input');
								}, 0);
								// En SweetAlert v1 con input, el callback recibe:
								// - false si cancelan
								// - string (valor del input) si confirman
								if (inputValue === false) return false;
								var motivo = '';
								motivo = (typeof inputValue === 'string' ? inputValue : '').trim();
								if (!motivo) {
									if (window.swal && typeof window.swal.showInputError === 'function') {
										window.swal.showInputError('El motivo es requerido.');
									}
									// Algunas builds cierran aunque retornes false; re-forzamos el modal abierto.
									setTimeout(function () {
										try {
											var modal = document.querySelector('.sweet-alert');
											if (modal) modal.style.display = 'block';
											var overlay = document.querySelector('.sweet-overlay');
											if (overlay) overlay.style.display = 'block';
										} catch (e) {}
									}, 0);
									return false;
								}

								var hidden = form.querySelector('input[name="motivo_incidencia"]');
								if (!hidden) {
									hidden = document.createElement('input');
									hidden.type = 'hidden';
									hidden.name = 'motivo_incidencia';
									form.appendChild(hidden);
								}
								hidden.value = motivo;
								form.submit();
						});
						setTimeout(ensureSwalCloseButton, 0);
							setTimeout(function () {
								var modal = document.querySelector('.sweet-alert');
								if (modal) modal.classList.add('show-input');
							}, 0);
						return;
					}

					if (confirm(texto)) {
						form.submit();
					}
				});
			});

			// Confirmación para DESMARCAR (solo super usuario) - sin pedir motivo
			document.querySelectorAll('[data-desincidencia-form]').forEach(function (form) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();

					const titulo = 'Desmarcar incidencia';
					const texto = '¿Confirmas que deseas desmarcar este expediente como incidencia?';

					if (typeof window.swal === 'function') {
						window.swal({
							title: titulo,
							text: texto,
							type: 'warning',
							allowEscapeKey: true,
							allowOutsideClick: true,
							showCancelButton: true,
							confirmButtonText: 'Sí, desmarcar',
							cancelButtonText: 'Cancelar',
							confirmButtonColor: '#198754'
						}, function (isConfirm) {
							setTimeout(ensureSwalCloseButton, 0);
							if (isConfirm) form.submit();
						});
						setTimeout(ensureSwalCloseButton, 0);
						return;
					}

					if (confirm(texto)) form.submit();
				});
			});
		});
	</script>
@endsection
