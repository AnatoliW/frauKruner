<x-front_app>
    @section('title','FrauKruner – Hilfe')
    @section('description','Du hast Fragen? Wir haben Antworten!')

    @push('css')
    <style>
        .faq-list-collapsing {
  margin-bottom: 1rem;
  display: block;
}
.faq-list-collapsing .faq-collapse-button {
  color: #122253;
  border: 0;
  background: none;
  padding: 0.55rem 0;
  margin: 0;
  font-size: 1.875rem;
  line-height: 1.913rem;
  font-weight: 700;
  text-align: left;
}
.faq-list-collapsing .faq-collapse-button .arrow {
  width: 1.25rem;
  height: 1.25rem;
  margin-right: 0.5rem;
  display: inline-block;
  position: relative;
  transition: 0.2s ease-in-out;
}
.faq-list-collapsing .faq-collapse-button .arrow span {
  top: 0.8rem;
  position: absolute;
  width: 0.75rem;
  height: 0.1rem;
  background-color: #122253;
  display: inline-block;
  transition: 0.2s ease-in-out;
}
.faq-list-collapsing .faq-collapse-button .arrow span:first-of-type {
  left: 0;
  transform: rotate(-45deg);
  transition: 0.2s ease-in-out;
}
.faq-list-collapsing .faq-collapse-button .arrow span:last-of-type {
  right: 0;
  transform: rotate(45deg);
  transition: 0.2s ease-in-out;
}
.faq-list-collapsing .faq-collapse-button[aria-expanded=false] .arrow span:first-of-type {
  transform: rotate(45deg);
  transition: 0.2s ease-in-out;
}
.faq-list-collapsing .faq-collapse-button[aria-expanded=false] .arrow span:last-of-type {
  transform: rotate(-45deg);
  transition: 0.2s ease-in-out;
}

.faq-collapse-button-closed {
  color: #122253;
  border: 0;
  background: none;
  padding: 0.55rem 0;
  margin: 0;
  font-size: 1.175rem;
  line-height: 1.313rem;
  font-weight: 700;
  text-align: left;
}
.faq-collapse-button-closed .arrow {
  width: 1.25rem;
  height: 1.25rem;
  margin-right: 0.5rem;
  display: inline-block;
  position: relative;
  transition: 0.2s ease-in-out;
}
.faq-collapse-button-closed .arrow span {
  top: 0.9rem;
  position: absolute;
  width: 0.75rem;
  height: 0.09rem;
  background-color: #122253;
  display: inline-block;
  transition: 0.2s ease-in-out;
}
.faq-collapse-button-closed .arrow span:first-of-type {
  left: 0;
  transform: rotate(45deg);
  transition: 0.2s ease-in-out;
}
.faq-collapse-button-closed .arrow span:last-of-type {
  right: 0;
  transform: rotate(-45deg);
  transition: 0.2s ease-in-out;
}
.faq-collapse-button-closed.collapsed .arrow span:first-of-type {
  transform: rotate(-45deg);
  transition: 0.2s ease-in-out;
}
.faq-collapse-button-closed.collapsed .arrow span:last-of-type {
  transform: rotate(45deg);
  transition: 0.2s ease-in-out;
}

.collapse_faq__sidebar {
  display: flex;
  flex-direction: column;
  margin-bottom: 1rem;
  margin-left: 1.8rem;
}
.collapse_faq__sidebar ul {
  padding: 0 0 0 1.2rem;
  margin: 0;
}
.collapse_faq__sidebar ul li {
  list-style: none;
  padding: 0 1rem;
  margin: 0;
}
    </style>

    @endpush
    <main class="container mt-5 mb-5" itemscope itemtype="https://schema.org/FAQPage">
        <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">FAQ</font></font></h1>

    <section class="mt-5 p-3">
        <div class="row">
            <div class="col-12 col-xl-6">
                <h3 class="mt-2 mb-5 text-primary"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Für Verkäufer</font></font></h3>

               @foreach ($for_sellers as $seller)
               <div class="faq-list-collapsing" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                <button class="faq-collapse-button-closed collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVerkauferinWerden-{{$seller->id}}" aria-expanded="false" aria-controls="collapseVerkauferinWerden-{{$seller->id}}"  itemprop="name">
                    <span class="arrow"><span></span><span></span></span><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">{{ $seller->question }}
                </font></font></button>

                <div class="collapse_faq__sidebar collapse" id="collapseVerkauferinWerden-{{$seller->id}}" style=""  itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                   <p itemprop="text">{!! $seller->answer !!}</p>
                </div>
            </div>
               @endforeach




            </div>

            <div class="col-12 col-xl-6">
                <h3 class="mt-2 mb-5 text-primary"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Für Käufer</font></font></h3>
                @foreach ($for_buyers as $buyer)
                <div class="faq-list-collapsing"  itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <button class="faq-collapse-button-closed collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWieKaufen-{{$buyer->id}}" aria-expanded="false" aria-controls="collapseWieKaufen-{{$buyer->id}}"itemprop="name">
                        <span class="arrow"><span></span><span></span></span><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">{{ $buyer->question }}
                    </font></font></button>

                    <div class="collapse_faq__sidebar collapse" id="collapseWieKaufen-{{$buyer->id}}" style="" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <p itemprop="text">{!! $buyer->answer !!}</p>
                    </div>
                </div>
                @endforeach


            </div>

        </div>


    </section>

    </main>
</x-front_app>
