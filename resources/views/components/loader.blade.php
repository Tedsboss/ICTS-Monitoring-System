<style>
#loader{
    position:fixed;
    inset:0;
    width:100%;
    height:100%;
    background:rgba(58,58,58,.9);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:99999;
}
.loader-content{text-align:center}
.loader-logo{
    width:200px;
    height:200px;
    object-fit:contain;
}
.loader-line{
    width:200px;
    height:4px;
    background-color:lightgray;
    margin-top:40px;
    position:relative;
    overflow:hidden;
}
.loader-line::before{
    content:"";
    position:absolute;
    top:0;
    left:-100%;
    width:100%;
    height:100%;
    background-color:#0038A8;
    animation:loading 1.5s infinite;
}
@keyframes loading{
    0%{left:-100%}
    50%{left:0}
    100%{left:100%}
}
</style>
<div id="loader">
    <div class="row ml-auto mr-auto justify-content-center">
        <div class="loader-content">
            <img src="/assets/img/neda/logowhite.png" alt="D.I.R.E.K. Logo" class="loader-logo">
            <div class="loader-line"></div>
        </div>
    </div>
</div>
