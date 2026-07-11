<x-app>

    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="card shadow-lg p-3">

        <form action="{{ route('user.store') }}" method="post" enctype="multipart/form-data" class="form">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label for="avatar" class="form-label">Avatar</label>
                    <input class="form-control @error('avatar') is-invalid  @enderror" type="file" id="upload"
                        name="avatar">
                    @error('avatar')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                    <img src="{{ asset('niceadmin/img/noprofil.png') }}" alt="Avatar" class="w-100 rounded mt-2"
                        id="preview">
                </div>

                <div class="col-md-9">
                    <div class="mb-3">
                        <label for="name" class="form-label required">Nama</label>
                        <input class="form-control @error('name') is-invalid  @enderror" type="text" id="name"
                            name="name" required value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label required">Email</label>
                        <input class="form-control @error('email') is-invalid  @enderror" type="email" id="email"
                            name="email" required value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label required">Password</label>
                        <input class="form-control @error('password') is-invalid  @enderror" type="password"
                            id="password" name="password" required minlength="8">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="passwordconfirm" class="form-label required">Konfirmasi Password</label>
                        <input class="form-control @error('passwordconfirm') is-invalid  @enderror" type="password"
                            id="passwordconfirm" name="passwordconfirm" required data-parsley-equalto="#password">
                        @error('passwordconfirm')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label required">Role</label>
                        <select class="form-select select2-default @error('role') is-invalid  @enderror" id="role"
                            name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="Superadmin" @selected(old('role') == 'Superadmin')>Superadmin</option>
                            <option value="Admin" @selected(old('role') == 'Admin')>Admin</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="text-end">
                <a href="{{ route('user.index') }}" class="btn btn-warning me-1">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>



        </form>

    </div>

</x-app>
