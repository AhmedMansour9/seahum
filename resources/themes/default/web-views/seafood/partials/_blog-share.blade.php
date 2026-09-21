@php($encodedShareUrl = urlencode($blogShareUrl))

<div class="sf-blog-share">
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedShareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ translate('share_on') }} Facebook">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8.5V6.8c0-.8.5-1.3 1.4-1.3H17V2.3A22 22 0 0 0 14.6 2C12 2 10.5 3.6 10.5 6.3v2.2H8V12h2.5v10H14V12h2.7l.4-3.5z"></path></svg>
    </a>
    <a href="https://twitter.com/intent/tweet?text={{ $encodedShareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ translate('share_on') }} X">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.8 2.5h3.3l-7.2 8.2 8.5 10.8h-6.6l-5.2-6.7-5.9 6.7H1.4l7.7-8.8L1 2.5h6.8l4.7 6.1zm-1.2 17h1.8L7.2 4.3H5.3z"></path></svg>
    </a>
    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ $encodedShareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ translate('share_on') }} LinkedIn">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4V21H3zm7 0h3.8v1.6h.1c.5-1 1.8-2 3.8-2 4 0 4.8 2.6 4.8 6V21h-4v-5.2c0-1.3 0-2.9-1.8-2.9s-2 1.4-2 2.8V21h-4z"></path></svg>
    </a>
    <a href="https://api.whatsapp.com/send?text={{ $encodedShareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ translate('share_on') }} WhatsApp">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.8-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.2-.2-.4-.3z"></path></svg>
    </a>
</div>
