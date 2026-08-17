
    @if (isset($Banner) && $Banner != null)


    <section class="text-center banner-two home-three-banner">
        <div class="auto-container">
            <div class="content-box">
                <br>

                <style>


                    .modal-body iframe {
                      width: 100%;
                      height: 60vh;
                    }
                  </style>



                <h2>
                    {{ $Banner->description }}
                </h2>
             <!-- For larger screens -->
<div class="btn-box d-none d-md-flex">
    @if(isset($Banner->is_button_video) && $Banner->is_button_video == true)
    <a href="javascruot:viod()" class="video-btn"  onclick="openVideoModal('{{ $Banner->button_video_link ?? null }}')">
      <i class="icon-3"></i>
    </a>
  @endif
    @if(isset($Consulting->is_active) && $Consulting->is_active == true)
    <a href="{{ $Banner->button_link }}" class="mx-5 theme-btn"><span>{{ $Banner->button_text }}</span></a>
    @endif
</div>

<!-- For phones only -->
<div class="btn-box d-flex d-md-none flex-column">
    @if(isset($Consulting->is_active) && $Consulting->is_active == true)
    <a href="{{ $Banner->button_link }}" class="mx-5 theme-btn"><span>{{ $Banner->button_text }}</span></a>
    @endif
    @if(isset($Banner->is_button_video) && $Banner->is_button_video == true)
    <a href="javascruot:viod()" class="video-btn"  onclick="openVideoModal('{{ $Banner->button_video_link ?? null }}')">
      <i class="icon-3"></i>
    </a>
  @endif
</div>




<div class="modal fade" id="videoModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
    <div class="modal-content"
        style="background-image: url({{ asset('front/assets/images/Imafgdggfe@2x.png') }}); background-size: cover; background-position: center;">
        <div class="modal-header">
          <button type="button" class="btn-close" style="    top: 0;" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <iframe id="videoFrame" loading="lazy" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </div>




            </div>

        </div>
    </section>

    @endif


