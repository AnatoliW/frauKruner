<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <lastmod>{{ now('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>
    <url>
        <loc>{{ route('blog') }}</loc>
        <lastmod>{{ now('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>
    <url>
        <loc>{{ route('cart') }}</loc>
        <lastmod>{{ now('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>
    <url>
        <loc>{{ route('checkout') }}</loc>
        <lastmod>{{ now('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>
    <url>
        <loc>{{ route('contact') }}</loc>
        <lastmod>{{ now('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>


    <url>
        <loc>{{ route('faq') }}</loc>
        <lastmod>{{ now('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>
    <url>
        <loc>{{ route('newpage') }}</loc>
        <lastmod>{{ now('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>
    <url>
        <loc>{{ route('vendors') }}</loc>
        <lastmod>{{ now('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>
    @foreach ($categories as $category)
        @if ($category->slug)
            <url>
                <loc>{{ route('shop', ['category' => $category->slug]) }}</loc>
                <lastmod>{{ $category->created_at->tz('UTC')->toAtomString() }}</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.51</priority>
            </url>
        @endif
    @endforeach
    @foreach ($postCats as $cat)
        @if ($cat->slug)
            <url>
                <loc>{{ route('blog', ['category' => $cat->slug]) }}</loc>
                <lastmod>{{ $cat->created_at->tz('UTC')->toAtomString() }}</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.51</priority>
            </url>
        @endif
    @endforeach
    @foreach ($products as $product)
        @if ($product->slug)
            <url>
                <loc>{{ route('product', ['slug' => $product->slug]) }}</loc>
                <lastmod>{{ $product->created_at->tz('UTC')->toAtomString() }}</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.51</priority>
            </url>
        @endif
    @endforeach
    @foreach ($vendors as $vendor)
        <url>
            <loc>{{ route('user.profile', $vendor) }}</loc>
            <lastmod>{{ $vendor->created_at->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.51</priority>
        </url>
    @endforeach
    @foreach ($posts as $post)
        <url>
            <loc>{{ route('post_details', $post->slug) }}</loc>
            <lastmod>{{ $post->created_at->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.51</priority>
        </url>
    @endforeach
    @foreach ($pages as $page)
        <url>
            <loc>{{ route('page', ['slug' => $page->slug]) }}</loc>
            <lastmod>{{ $page->created_at->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.51</priority>
        </url>
    @endforeach
</urlset>
