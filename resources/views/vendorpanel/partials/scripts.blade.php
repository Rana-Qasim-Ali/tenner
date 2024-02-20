<script>
  'use strict';

  const baseUrl = "{{ url('/') }}";
  var loadImgs = '';
  var ProductloadImgs = '';
</script>

{{-- js color --}}
<script type="text/javascript" src="{{ asset('assets/js/core/popper.min.js') }}"></script>

{{-- dropzone js --}}
<script type="text/javascript" src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/material-dashboard.min.js?v=3.0.0') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('assets/js/admin-main.js') }}"></script>


{{-- 
@if (session()->has('success'))
  <script>
    "use strict";
    var content = {};

    content.message = '{{ __(session('success')) }}';
    content.title = 'Success';
    content.icon = 'fa fa-bell';

    $.notify(content, {
      type: 'success',
      placement: {
        from: 'top',
        align: 'right'
      },
      showProgressbar: true,
      time: 1000,
      delay: 4000
    });
  </script>
@endif --}}

{{-- @if (session()->has('warning'))
  <script>
    "use strict";
    var content = {};

    content.message = '{{ __(session('warning')) }}';
    content.title = 'Warning!';
    content.icon = 'fa fa-bell';

    $.notify(content, {
      type: 'warning',
      placement: {
        from: 'top',
        align: 'right'
      },
      showProgressbar: true,
      time: 1000,
      delay: 4000
    });
  </script>
@endif --}}

<script>
  'use strict';
  const account_status = 1;
  const secret_login = 1;
</script>

@yield('variables')

@yield('script')
@yield('vuescripts')
