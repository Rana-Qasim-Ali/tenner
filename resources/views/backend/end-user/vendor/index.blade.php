@extends('backend.layout')

@section('content')
  {{-- <div class="page-header">
    <h4 class="page-title">{{ __('Registered Organizers') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Organizers Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Registered Organizers') }}</a>
      </li>
    </ul>
  </div> --}}
  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-12">
        <div class="card my-4">
          <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
              <h6 class="text-white text-capitalize ps-3">Vendor List</h6>
            </div>
          </div>
          <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
              <table class="table align-items-center mb-0" id="end_user_vendors">
                <thead>
                  <tr>
                    {{-- <th scope="col">
                      <input type="checkbox" class="bulk-check" data-val="all">
                    </th> --}}
                    <th scope="col">{{ __('Company Name') }}</th>
                    <th scope="col">{{ __('Email ID') }}</th>
                    <th scope="col">{{ __('Phone') }}</th>
                    {{-- <th scope="col">{{ __('Account Status') }}</th> --}}
                    {{-- <th scope="col">{{ __('Email Status') }}</th> --}}
                    <th scope="col">{{ __('Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>

@endsection
  @section('script')
  <script>
    var myTable = $('#end_user_vendors').DataTable({
        dom: "Blfrtip",
        ordering: false,
        buttons: ["copy", "csv", "excel", "pdf", "print"],
        processing: true,
        serverSide: true,
        ajax: {
            url:"{{ route('admin.vendor_management.get_vendor') }}",
            dataType:"json",
            type: "GET",
        }, 
        columns: [
          // { data: 'checkbox', name: 'checkbox'},
          { data: 'company_name', name: 'company_name'},
          { data: 'email', name: 'email' },
          { data: 'phone', name: 'phone' },
          // { data: 'account_status', name: 'account_status' },
          // { data: 'email_status', name: 'email_status' },
          { data: 'actions', name: 'actions' },
        ],
        lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ]
      });
  
  </script>
@endsection
