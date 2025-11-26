@extends('layouts.connect')

@section('menu.sessions','active')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">WA Sessions</h5>
    <div>
        <div class="d-inline-flex align-items-center">
            <input id="sessionName" type="text" class="form-control mr-2" value="{{ $default }}" placeholder="nama-session" style="width: 220px;">
            <button id="btnOpenQr" class="btn btn-success">Buka QR</button>
        </div>
        <button id="btnOpenQrDefault" class="btn btn-secondary ml-2" {{ $startUrl ? '' : 'disabled' }}>Buka QR (Default)</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Session</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @php($list = (is_array($sessions) && isset($sessions['data']) && is_array($sessions['data'])) ? $sessions['data'] : [])
                @forelse($list as $sess)
                    <tr>
                        <td>{{ $sess }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.sessions.logout') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="session" value="{{ $sess }}">
                                <button class="btn btn-sm btn-danger">Logout</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">Tidak ada session aktif</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Scan QR WhatsApp</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="height:520px;">
        <iframe id="qrFrame" src="about:blank" style="width:100%; height:100%; border:0;"></iframe>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var base = "{{ $baseUrl }}";
  var defaultStart = "{{ $startUrl }}";
  var btn = document.getElementById('btnOpenQr');
  var inp = document.getElementById('sessionName');
  var frame = document.getElementById('qrFrame');
  var btnDef = document.getElementById('btnOpenQrDefault');
  function openModal(url){ frame.src = url; $('#qrModal').modal('show'); }
  btn.addEventListener('click', function(){ var s = inp.value || ''; var url = base + '/session/start?session=' + encodeURIComponent(s); openModal(url); });
  btnDef.addEventListener('click', function(){ if (defaultStart) openModal(defaultStart); });
});
</script>
@endsection
