{{--
    Aviso de proceso vencido (SweetAlert2). Lo manda SeerController::
    borradorSolicitudAuxVencido cuando una pestaña vieja intenta seguir una
    solicitud cuyo borrador ya se borró (se venció o se canceló).
--}}
@if (session('proceso_vencido'))
    @push('scripts')
        <script>
            (function () {
                function avisar() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Hay que volver a empezar',
                        html: 'Esta solicitud ya no se pudo continuar porque el proceso <b>se venció por inactividad o se canceló</b> desde otra pestaña.<br><br>No se guardó nada. Inicia la solicitud de nuevo desde aquí.',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#496163'
                    });
                }
                if (window.Swal) { avisar(); return; }
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                s.onload = avisar;
                document.head.appendChild(s);
            })();
        </script>
    @endpush
@endif
