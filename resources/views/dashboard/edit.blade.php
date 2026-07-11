<x-app>

    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="card shadow-lg p-3">

        <form action="{{ route('dashboard.update') }}" method="post" enctype="multipart/form-data" class="form">
            @csrf
            @method('put')

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
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('niceadmin/img/noprofil.png') }}"
                        alt="Avatar" class="w-100 rounded mt-2" id="preview">
                </div>

                <div class="col-md-9">
                    <div class="mb-3">
                        <label for="name" class="form-label required">Nama</label>
                        <input class="form-control @error('name') is-invalid  @enderror" type="text" id="name"
                            name="name" required value="{{ old('name', $user->name) }}">
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label required">Email</label>
                        <input class="form-control @error('email') is-invalid  @enderror" type="email" id="email"
                            name="email" required value="{{ old('email', $user->email) }}">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label ">Password</label>
                        <input class="form-control @error('password') is-invalid  @enderror" type="password"
                            id="password" name="password" minlength="8">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="passwordconfirm" class="form-label ">Konfirmasi Password</label>
                        <input class="form-control @error('passwordconfirm') is-invalid  @enderror" type="password"
                            id="passwordconfirm" name="passwordconfirm" data-parsley-equalto="#password">
                        @error('passwordconfirm')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="text-end">
                <a href="{{ route('dashboard.index') }}" class="btn btn-warning me-1">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>

        </form>

    </div>

</x-app>
