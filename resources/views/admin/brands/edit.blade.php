@extends('admin.layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Brand</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{route('brands.index')}}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            <form action="" method="post" id="createBrandForm" name="createBrandForm">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name">Name</label>
                                    <input type="text" value="{{ $brand->name }}" name="name" id="name" class="form-control" placeholder="Name">
                                    <p></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email">Slug</label>
                                    <input type="text" readonly name="slug" id="slug" value="{{ $brand->slug }}" class="form-control" placeholder="Slug">
                                    <p></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option {{ ($brand->status == 1) ?'selected': '' }} value="1">Active</option>
                                        <option {{ ($brand->status == 0) ?'selected': '' }} value="0">Block</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pb-5 pt-3">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <a href="{{route('brands.index')}}" class="btn btn-outline-dark ml-3">Cancel</a>
                </div>
            </form>
        </div>
        <!-- /.card -->
    </section>
    <!-- /.content -->
@endsection

@section('customJs')
    <script type="text/javascript">
        $('#createBrandForm').submit(function(e){
            e.preventDefault();
            $("button[type=submit]").prop('disabled',true);
            $.ajax({
                url: '{{ route("brands.update", $brand->id) }}',
                type:'put',
                dataType: 'json',
                data: $("#createBrandForm").serializeArray(),
                success: function (response){
                    $("button[type=submit]").prop('disabled',false);
                    if(response.status == true){
                        $("#name").removeClass('is-invalid')
                            .siblings('p')
                            .removeClass('invalid-feedback')
                            .html('')
                        $("#slug").removeClass('is-invalid')
                            .siblings('p')
                            .removeClass('invalid-feedback')
                            .html('')
                        window.location.href="{{ route('brands.index') }}";
                    }else{
                        if(response['notFound'] == true){
                            window.location.href="{{ route('brands.index') }}";
                        }
                        var errors = response.errors;
                        if (errors.name) {
                            $("#name").addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.name)
                        } else {
                            $("#name").removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }

                        if (errors.slug) {
                            $("#slug").addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.slug)
                        } else {
                            $("#slug").removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }
                    }
                },
                error:function(jqXHR, exception){
                    console.log("Somethink went wrong");
                }
            });
        });

        $('#name').change(function(){
            element = $(this);
            $("button[type=submit]").prop('disabled',true);
            $.ajax({
                url:'{{ route("getSlug") }}',
                type:'get',
                dataType: 'json',
                data: {title: element.val()},
                success: function(response){
                    $("button[type=submit]").prop('disabled',false);
                    if(response.status == true){
                        $('#slug').val(response.slug);
                    }
                }
            });
        });




    </script>
@endsection
