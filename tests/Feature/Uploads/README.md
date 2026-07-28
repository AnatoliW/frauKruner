# Upload tests (photos & videos)

Covers every place where users can upload files.

| Feature | Route | Controller |
|---|---|---|
| Product thumbnail + gallery | `POST seller/dashboard/products/create` · `.../{p}/update` | `Seller\ProductsController::store/update` |
| Photos for an order | `POST seller/dashboard/photo/upload` · `photo/delete/{img}` | `Seller\PagesController::photoUpload/photoDelete` |
| Video for an order | `POST seller/dashboard/video/upload` | `Seller\PagesController::VideoUpload` |
| Profile picture | `POST /info/updated` | `ProfileController::info` |
| ID documents | `POST /verification/updated` | `ProfileController::verification` |

The tests run against an **in-memory SQLite database** and **faked storage
disks** (`Storage::fake`). They touch neither the real database nor S3, so
they are safe to run on the production server.

This application's legacy schema is not managed by migrations, so the tables
the tests need are built by `tests/Support/UploadTestSchema.php`.

## How large may a video be?

**The application imposes no limit at all.** There is not a single validation
rule for the `video` field — no `max`, no `mimetypes`, nothing. The
"max. 1 GB in .mp4" is display text in the Blade template only. The real
limit therefore comes entirely from the server, as the minimum across
several layers:

| Layer | Setting | Applies to |
|---|---|---|
| Web server | nginx `client_max_body_size` / Apache `LimitRequestBody` | whole request |
| PHP | `post_max_size` | whole request |
| PHP | `upload_max_filesize` | individual file |
| PHP / web server | `max_input_time`, `client_body_timeout`, FPM `request_terminate_timeout` | duration |
| Disk | free space in `upload_tmp_dir` | PHP buffers the **entire** file there before Laravel ever sees it |

Measured locally: **32 MB effective** (`upload_max_filesize = 32M`,
`post_max_size = 40M`) — about 3 % of what the interface promises. Use
`uploads:diagnose` and `uploads:probe-limit` (below) to get the numbers for
the production server.

Worth keeping in mind: even with every layer raised to 1 GB, a typical
seller's upstream (2–10 Mbit/s) means **15 to 70 minutes** per upload. Any
timeout in the chain aborts it — and because of finding 6 below, she still
sees "Video erfolgreich hochgeladen." A lower advertised limit or a chunked
direct-to-S3 upload is the realistic fix.

## Running the tests

```bash
# 1) Functional tests – must be green
php artisan test tests/Feature/Uploads \
    --exclude-group=upload-bugs --exclude-group=umgebung

# 2) Server environment – PHP limits, exiftool
php artisan test tests/Feature/Uploads --group=umgebung

# 3) Known gaps – document what is currently NOT covered
php artisan test tests/Feature/Uploads --group=upload-bugs

# everything
php artisan test tests/Feature/Uploads
```

## Checking the real server

The tests use fakes, so they cannot see the actual configuration. Two
commands do:

```bash
php artisan uploads:diagnose            # read-only
php artisan uploads:diagnose --write    # additionally create/read/delete a file on the disks
```

`uploads:diagnose` checks PHP limits, extensions, `exiftool`/`ffmpeg`,
directory permissions, storage disks and the database columns the upload
code writes to. It exits with code 1 as soon as a required check fails, so it
can go straight into a deploy script.

Run it as the **web server user**: PHP-CLI and PHP-FPM/Apache usually load
different `php.ini` files, and only the latter matters for uploads.

## Measuring the real maximum video size

The Pest tests **cannot** verify the size limit: they build the request in
memory, so the web server and PHP's multipart parser never come into play.
A separate command sends real HTTP uploads of increasing size instead:

```bash
php artisan uploads:probe-limit
php artisan uploads:probe-limit --url=https://www.fraukruner.de --sizes=100,500,1024
```

The upload deliberately runs **without logging in**. The route sits behind
the `auth` middleware, so the server accepts the complete body and only then
redirects to `/login` — no record is created and no file is stored. The CSRF
token is sent as a form field, not a header: only then does it disappear
along with the rest of the POST data when `post_max_size` is exceeded, which
makes the failure visible in the status code.

| HTTP | Meaning |
|---|---|
| 302 → `/login` | request arrived in full |
| 413 | `post_max_size` or web server limit (`client_max_body_size` / `LimitRequestBody`) |
| 419 | POST data discarded, CSRF token gone → `post_max_size` too small |
| 502 / 504 | proxy or FPM gives up before the upload finishes |
| no response | timeout in the web server or proxy |

This measures the limit of **web server + `post_max_size`**.
`upload_max_filesize` additionally applies *per file* and is not observable
from outside — PHP discards only the file while the request itself gets
through. `uploads:diagnose` reports that value on the
"Effektiv größtes Video" line.

## Test groups

- *(no group)* – functional tests. Must be green.
- `umgebung` – checks the server, not the code: `upload_max_filesize`,
  `post_max_size`, `max_file_uploads`, temp directory, `exiftool`.
- `upload-bugs` – tests that describe the **desired** behaviour and are
  currently **red**. They are the to-do list below. Once a gap is closed the
  corresponding test turns green and can leave the group.

## Current status (checked locally, 2026-07-28)

Functional tests: **52 green, 0 red** (2 skipped because `exiftool` is not
installed locally).

### `upload-bugs` – 12 red tests

1. **Photo upload does not check the order.** `photoUpload()` validates only
   `order_id`. Any logged-in seller can attach photos to *anyone else's*
   order. → `OrderPhotoUploadTest`
2. **Photo deletion does not check ownership.**
   `photoDelete(Orderimage $orderimage)` deletes without any check, so
   other people's photos can be removed. → `OrderPhotoUploadTest`
3. **Photo upload does not check the file type.** There is no
   `image`/`mimes` rule for `upload_photo[]` on the server side. The check
   exists only in the Blade file's JavaScript and is therefore worthless.
   → `OrderPhotoUploadTest`
4. **Video upload does not check the file type.** `.php`, `.zip` and `.xlsx`
   end up in storage as "videos". → `OrderVideoUploadTest`
5. **Video upload does not check the size.** The 1 GB limit exists only as
   Blade text; there is no `max` rule on the server. The seller uploads a
   full gigabyte and is told nothing. → `OrderVideoUploadTest`
6. **Failed video uploads report success.** When PHP discards the video —
   because it exceeds `upload_max_filesize`, because the transfer was
   interrupted, because the temp directory is missing or the disk is full —
   the request still arrives, just without the file. `hasFile('video')` is
   then false and `VideoUpload()` falls **unconditionally** into
   `back()->with('success', 'Video erfolgreich hochgeladen.')` at the end.
   The seller sees the success message; nothing was saved. This is the most
   likely cause of "my video disappeared" reports.
   → `OrderVideoUploadTest`, dataset over 4 PHP upload error codes
7. **webp thumbnails are never stored.** `ProductsController::store()` allows
   `webp` in validation but only writes `jpeg|jpg|png`. For webp it sets
   `$fullPath = $request->thumbnail` instead, so `products.image` ends up
   holding the temporary upload path (`/private/var/folders/.../phpXXXX`)
   while the file itself exists nowhere. → `ProductImageUploadTest`

### `umgebung` – server-dependent

8. **`exiftool` is not installed.** `ExifMetadataService` runs
   `exec("exiftool -all= ...")` and **always returns `true`**, even when the
   command does not exist. EXIF and GPS data therefore stay in the images
   while `meta_remove_status` in the database is set to 1 anyway. For
   sellers' photos that is their home address.
9. **PHP limits too small.** Locally `upload_max_filesize = 32M` and
   `post_max_size = 40M` — a 1 GB video is impossible, and the browser aborts
   without a usable error message.

### Additionally found by `uploads:diagnose` (not part of the tests)

- **Missing database columns** in the local database:
  `products.meta_remove_status`, `orderimages.meta_remove_status`,
  `profiles.meta_remove_status`. The upload code writes to these columns; if
  they are absent the upload ends in an SQL error instead of a message.
  Verify this on the production server.
- **Default disk vs. `s3`.** `VideoUpload()` and
  `ProductsController::update()` hard-code `Storage::disk('s3')` for checking
  and deleting, but store the file via `->store()` on the **default disk**.
  If the two differ, old videos and images are never cleaned up.
- **`FILESYSTEM_DRIVER` in `.env`** is the old Laravel 8 key. Since Laravel 9
  it is `FILESYSTEM_DISK`; the old value is ignored and the application falls
  back to `local`.
- **Video metadata is not stripped.** `StripeVideoMetaData` exists, but the
  call in `VideoUpload()` is commented out. Videos keep their geolocation and
  device identifiers.

## Adding a new upload feature

1. Add any missing tables/columns to `tests/Support/UploadTestSchema.php`,
   and register them in `REQUIRED_COLUMNS` as well as in
   `DiagnoseUploads::checkDatabaseColumns()`.
2. Create a test file in this folder. `uploads()` provides the factories:
   `seller()`, `buyer()`, `order()`, `image()`, `video()`, `realVideo()`,
   `rejectedVideo()`, `disguisedPhpFile()`, `imageWithMetadata()`.
3. Run storage assertions with `->with('disks')` against both `local` **and**
   `s3`.

### Choosing the right fake file

| Helper | Produces | Use for |
|---|---|---|
| `video($name, $kb)` | file that *reports* `$kb`, empty on disk | fast validation checks |
| `realVideo($name, $mb)` | sparse file with real bytes, `filesize()` correct | anything size-related |
| `rejectedVideo($error)` | empty tmp_name + PHP upload error code | what PHP delivers when a limit is hit |
| `disguisedPhpFile($name)` | PHP source with an image extension | MIME sniffing |
| `imageWithMetadata($name)` | valid JPEG with a metadata marker | EXIF stripping |

`UploadedFile::fake()->create()` only *reports* its size — the file on disk
is empty. That is fine for validation, but useless for anything that depends
on real bytes, which is why `realVideo()` exists.
