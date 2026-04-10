@extends('layouts.app')

@section('header')
<h1>Asistencia</h1>
<p>Registra tu asistencia</p>
@endsection

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

@php
$colors = ['btn-outline-primary', 'btn-outline-success', 'btn-outline-info', 'btn-outline-warning', 'btn-outline-danger', 'btn-outline-dark'];
@endphp

<div class="row">
    <div class="col-md-12">
        <div class="card p-5">
            <div class="col-12">
                <h3>Hora actual</h3>
                <p id="current-time" class="fs-4 fw-bold"></p>
            </div>
            <div class="col-12 d-flex flex-column align-items-center" style="gap: 2rem">
                <div class="d-flex justify-content-center align-items-center w-100">
                    <div class="card text-center w-100" style="max-width: 400px;">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <div class="d-flex flex-column justify-content-center align-items-center mb-3 w-100" style="background: #fff; padding: 2rem 1rem;">
                                <button type="button" class="btn btn-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#openPinModal">
                                    Marcar asistencia
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="openPinModal" tabindex="-1">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Ingresa tu PIN</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form onsubmit="checkAttendance(event)" autocomplete="off">
					<div class="mb-3">
						<label class="form-label">PIN de empleado</label>
						<input type="password" class="form-control mb-2" id="start_pin" name="start_pin_field" maxlength="4" autocomplete="new-password" pattern="[0-9]*" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
						<table class="mx-auto">
							<tr>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(1, '#start_pin')">1</button></td>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(2, '#start_pin')">2</button></td>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(3, '#start_pin')">3</button></td>
							</tr>
							<tr>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(4, '#start_pin')">4</button></td>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(5, '#start_pin')">5</button></td>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(6, '#start_pin')">6</button></td>
							</tr>
							<tr>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(7, '#start_pin')">7</button></td>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(8, '#start_pin')">8</button></td>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(9, '#start_pin')">9</button></td>
							</tr>
							<tr>
								<td></td>
								<td><button type="button" class="btn btn-light w-100" onclick="keyPad(0, '#start_pin')">0</button></td>
								<td></td>
							</tr>
						</table>
					</div>
					<button type="submit" class="btn btn-primary w-100">Registrar</button>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="confirmAttendanceModal" tabindex="-1" aria-labelledby="confirmLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p><strong>Empleado:</strong> <span id="modalEmployeeName"></span></p>
        <p id="entranceElement"><strong>Entrada:</strong> <span id="modalEntryTime"></span></p>
        <p>¿Está seguro que desea registrar su <strong id="modalAttendanceType"></strong>?</p>
      </div>
      <div class="modal-footer">
		<form  onsubmit="confirmAttendance(event)" autocomplete="off">
			@csrf
			<input type="text" name="employee_id" id="employee_id" value="" hidden>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
			<button type="submit" class="btn btn-primary" id="confirmAttendanceBtn">Registrar</button>
		</form>
      </div>
    </div>
  </div>
</div>
    

<script>
    // Mostrar la hora actual
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        document.getElementById('current-time').textContent = timeString;
    }
    setInterval(updateTime, 1000);
    updateTime();

    function keyPad(number, el) {
		var value = $(el).val();

		if (value.length < 4) {
			$(el).val(value.toString() + number.toString());
		}
	}

    //llena el confirmattendance
    function checkAttendance(e) {

		e.preventDefault();

		var pin = $('#start_pin').val();

		$.ajax({
			url: '{{ route('attendance.check') }}',
			method: 'GET',
			data: {
				pin
			},
			success: function(data) {
				if (data.employee) {
					$('#modalEmployeeName').text(data.employee.name + ' ' + data.employee.last_name);
					$('#employee_id').val(data.employee_id);
					$('#confirmLabel').text('Confirmar ' + data.type);
					$('#modalAttendanceType').text(data.type);

					if (data.type === 'entrada') {
						$('#entranceElement').attr('hidden', true);
					} else if (data.type === 'salida') {
						$('#entranceElement').removeAttr('hidden');
						// Formatear fecha y hora
						if (data.start) {
							var dateObj = new Date(data.start.replace(' ', 'T'));
							var day = String(dateObj.getDate()).padStart(2, '0');
							var month = String(dateObj.getMonth() + 1).padStart(2, '0');
							var year = dateObj.getFullYear();
							var hours = dateObj.getHours();
							var minutes = String(dateObj.getMinutes()).padStart(2, '0');
							var ampm = hours >= 12 ? 'pm' : 'am';
							var hour12 = hours % 12;
							hour12 = hour12 ? hour12 : 12;
							var formatted = day + '/' + month + '/' + year + ' ' + hour12 + ':' + minutes + ' ' + ampm;
							$('#modalEntryTime').text(formatted);
						} else {
							$('#modalEntryTime').text('');
						}
					}

					$('#confirmAttendanceModal').modal('show');
					$('#openPinModal').modal('hide');
				} else {
					ToastError.fire({
						text: 'Empleado no encontrado'
					});
					$('#start_pin').val('');
				}
			},
			error: function() {
				ToastError.fire({
					text: 'Ocurrio un error'
				});
			}
		})
	}

	function confirmAttendance(e) {

		e.preventDefault();

		var form = e.target;
		var formData = new FormData(form);

		$.ajax({
			url: '{{ route('attendance.store') }}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(data) {
				if (data.status) {
					ToastMessage.fire({
						text: 'Asistencia registrada correctamente'
					});

					$('#confirmAttendanceModal').modal('hide');
					$('#openPinModal').modal('hide');
				} else {
					ToastError.fire({
						text: 'Ocurrio un error'
					});
				}
			},
			error: function() {
				ToastError.fire({
					text: 'Ocurrio un error'
				});
			}
		})
	}
                                             
	// Limpiar el campo PIN al cerrar cualquier modal
	document.addEventListener('DOMContentLoaded', function() {
		var modals = ['#openPinModal', '#confirmAttendanceModal'];
		modals.forEach(function(modalId) {
			$(modalId).on('hide.bs.modal', function () {
				$('#start_pin').val('');
			});
		});
	});
</script>
@endsection

