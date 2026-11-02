window.renderInfo=function(e,t){$(e).html(`
        Menampilkan
        <b>${t.from??0}</b>
        -
        <b>${t.to??0}</b>
        dari
        <b>${t.total}</b>
        data
    `)};window.renderPagination=function(e,t="loadData",n="#pagination"){let i="";const a=Number(e.current_page)||1,o=Number(e.last_page)||1;if(o<=1){$(n).html("");return}i+=`
        <button
            type="button"
            onclick="${t}(${a-1})"
            ${a===1?"disabled":""}
            class="w-10 h-10 shrink-0 rounded-xl
                   border border-slate-200
                   hover:bg-slate-100
                   disabled:opacity-50
                   disabled:cursor-not-allowed">

            <i class="fa-solid fa-angle-left"></i>

        </button>
    `;const s=[];if(o<=7)for(let r=1;r<=o;r++)s.push(r);else if(s.push(1),a<=4)s.push(2,3,4,5),s.push("..."),s.push(o);else if(a>=o-3){s.push("...");for(let r=o-4;r<=o;r++)s.push(r)}else s.push("..."),s.push(a-2),s.push(a-1),s.push(a),s.push(a+1),s.push(a+2),s.push("..."),s.push(o);s.forEach(function(r){if(r==="..."){i+=`
                <span
                    class="w-8 h-10 shrink-0
                           flex items-center justify-center
                           text-slate-400 select-none">

                    ...

                </span>
            `;return}i+=`
            <button
                type="button"
                onclick="${t}(${r})"
                class="w-10 h-10 shrink-0 rounded-xl
                    ${r===a?"bg-primary text-white":"border border-slate-200 hover:bg-slate-100"}">

                ${r}

            </button>
        `}),i+=`
        <button
            type="button"
            onclick="${t}(${a+1})"
            ${a===o?"disabled":""}
            class="w-10 h-10 shrink-0 rounded-xl
                   border border-slate-200
                   hover:bg-slate-100
                   disabled:opacity-50
                   disabled:cursor-not-allowed">

            <i class="fa-solid fa-angle-right"></i>

        </button>
    `,$(n).html(i)};window.hapusData=function({url:e,name:t,title:n="Hapus Data?",reload:i=null}){Swal.fire({title:n,html:`Yakin ingin menghapus <b>${t}</b>?`,icon:"warning",showCancelButton:!0,confirmButtonColor:"#dc2626",cancelButtonColor:"#64748b",confirmButtonText:"Ya, Hapus",cancelButtonText:"Batal"}).then(a=>{a.isConfirmed&&$.ajax({url:e,type:"POST",data:{_token:$('meta[name="csrf-token"]').attr("content"),_method:"DELETE"},success(o){Swal.fire({icon:"success",title:"Berhasil",text:o.message,timer:1500,showConfirmButton:!1}),typeof i=="function"&&i()},error(o){var s;Swal.fire({icon:"error",title:"Gagal",text:((s=o.responseJSON)==null?void 0:s.message)??"Terjadi kesalahan."})}})})};window.loadingSkeleton=function(e="#tableData",t=8,n=""){if(!n)return;let i="";for(let a=0;a<t;a++)i+=n;$(e).html(i)};window.skeleton={table:`
        <tr class="animate-pulse">

            <td class="px-6 py-5">
                <div class="h-4 w-6 rounded bg-slate-200"></div>
            </td>

            <td class="px-6 py-5">
                <div class="flex items-center gap-3">

                    <div class="w-11 h-11 rounded-full bg-slate-200"></div>

                    <div>
                        <div class="h-4 w-40 rounded bg-slate-200 mb-2"></div>
                        <div class="h-3 w-52 rounded bg-slate-100"></div>
                    </div>

                </div>
            </td>

            <td class="px-6 py-5">
                <div class="h-7 w-20 rounded-full bg-slate-200"></div>
            </td>

            <td class="px-6 py-5">
                <div class="h-4 w-24 rounded bg-slate-200"></div>
            </td>

            <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-slate-200"></div>
                    <div class="w-10 h-10 rounded-xl bg-slate-200"></div>
                    <div class="w-10 h-10 rounded-xl bg-slate-200"></div>
                </div>
            </td>

        </tr>
    `};window.debounceSearch=function(e,t,n=400){let i;$(document).on("keyup",e,function(){clearTimeout(i),i=setTimeout(t,n)})};window.refreshData=function(e="#btnRefresh",t="#search",n){$(document).on("click",e,function(){$(t).val(""),n()})};window.emptyTable=function(e,t,n){return`
        <tr>

            <td colspan="${e}" class="py-16">

                <div class="flex flex-col items-center">

                    <i class="fa-regular fa-folder-open text-6xl text-slate-300 mb-4"></i>

                    <h3 class="font-semibold text-slate-700 text-lg">
                        ${t}
                    </h3>

                    <p class="text-slate-400 mt-1">
                        ${n}
                    </p>

                </div>

            </td>

        </tr>
    `};window.tableError=function(e,t="Gagal mengambil data."){return`
        <tr>

            <td colspan="${e}"
                class="py-14 text-center text-red-500">

                ${t}

            </td>

        </tr>
    `};window.ajaxRequest=function({url:e,type:t="GET",data:n={},before:i=null,success:a=null,error:o=null,complete:s=null,dataType:r="json"}){typeof i=="function"&&i(),$.ajax({url:e,type:t,data:n,dataType:r,success(l){typeof a=="function"&&a(l)},error(l){var d;typeof o=="function"?o(l):Swal.fire({icon:"error",title:"Oops...",text:((d=l.responseJSON)==null?void 0:d.message)??"Terjadi kesalahan."})},complete(){typeof s=="function"&&s()}})};window.initDateRangePicker=function(e,t=null,n={}){let i={mode:"range",dateFormat:"d-m-Y",rangeSeparator:" s.d ",allowInput:!1,locale:{rangeSeparator:" s.d "},onChange(a,o,s){let r="",l="";a.length===2&&(r=s.formatDate(a[0],"Y-m-d"),l=s.formatDate(a[1],"Y-m-d")),typeof t=="function"&&t(r,l,a,s)}};return Object.assign(i,n),flatpickr(e,i)};window.formatAngka=function(e){return e==null||e===""?"0":new Intl.NumberFormat("id-ID").format(Number(e)||0)};window.getNominal=function(e){return e&&parseInt(e.toString().replace(/\./g,""))||0};window.formatDurasi=function(e){if(e=Math.abs(parseInt(e)||0),e===0)return"-";const t=Math.floor(e/60),n=e%60;return t>0&&n>0?`${t} Jam ${n} Menit`:t>0?`${t} Jam`:`${n} Menit`};window.formatJam=function(e){return e?new Date(e).toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit",second:"2-digit"}):"-"};window.capitalize=function(e){return e.charAt(0).toUpperCase()+e.slice(1)};window.toNumber=function(e){return e==null||e===""?0:typeof e=="number"?e:Number(String(e).replace(/Rp/gi,"").replace(/\./g,"").replace(/,/g,"").replace(/[^\d-]/g,""))||0};window.formatRupiah=function(e,t=!0){if(e==null||e==="")return t?"Rp. 0":"0";let n=Number(String(e).replace(/[^\d-]/g,""));isNaN(n)&&(n=0);const i=new Intl.NumberFormat("id-ID").format(Math.abs(n));return t?n<0?`- Rp. ${i}`:`Rp. ${i}`:n<0?`- ${i}`:i};window.formatRupiahDecimal=function(e,t=!0){if(e==null||e==="")return t?"Rp. 0":"0";let n=parseFloat(e);isNaN(n)&&(n=parseFloat(String(e).replace(/\./g,"").replace(",",".")));const i=new Intl.NumberFormat("id-ID",{maximumFractionDigits:0}).format(n);return t?`Rp. ${i}`:i};window.formatRupiahFull=function(e,t=!0){const n=Number(e)||0,i=new Intl.NumberFormat("id-ID",{minimumFractionDigits:2,maximumFractionDigits:2}).format(n);return t?`Rp. ${i}`:i};window.formatRupiahTanpaRp=function(e,t=!0){if(e==null||e==="")return t?"Rp. 0":"0";const n=Number(String(e).replace(/[^\d]/g,"")),i=new Intl.NumberFormat("id-ID").format(n);return t?`${i}`:i};window.formatTanggal=function(e,t="id-ID",n={}){if(!e)return"-";const i=new Date(e);if(isNaN(i.getTime()))return"-";const a={day:"2-digit",month:"2-digit",year:"numeric"};return new Intl.DateTimeFormat(t,{...a,...n}).format(i)};window.formatTanggalIndo=function(e){if(!e)return"-";const t=["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"],n=["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Ags","Sep","Okt","Nov","Des"],i=new Date(e);return isNaN(i.getTime())?"-":`${t[i.getDay()]}, ${i.getDate()} ${n[i.getMonth()]} ${i.getFullYear()}`};window.formatTanggalJam=function(e,t="id-ID"){if(!e)return"-";const n=new Date(e);return isNaN(n.getTime())?"-":new Intl.DateTimeFormat(t,{day:"2-digit",month:"2-digit",year:"numeric",hour:"2-digit",minute:"2-digit",second:"2-digit",hour12:!1}).format(n)};window.showModal=function(e){const t=document.getElementById(e);if(!t)return;const n=t.querySelector(".modal-content");if(!n)return;document.documentElement.classList.add("overflow-hidden"),document.body.classList.add("overflow-hidden"),t.classList.remove("hidden"),t.classList.add("flex","justify-center"),t.classList.remove("items-center","items-start"),n.classList.remove("mt-[5vh]","mt-[10vh]");function i(){if(t.classList.contains("hidden"))return;const a=window.innerHeight,o=n.scrollHeight;t.classList.remove("items-center","items-start"),n.classList.remove("mt-[5vh]","mt-[10vh]"),o+40<=a?t.classList.add("items-center"):(t.classList.add("items-start"),n.classList.add("mt-[5vh]"))}requestAnimationFrame(()=>{i(),t.classList.remove("opacity-0"),t.classList.add("opacity-100"),n.classList.remove("scale-95","-translate-y-6"),n.classList.add("scale-100","translate-y-0")}),window.ResizeObserver&&(t._modalResizeObserver&&t._modalResizeObserver.disconnect(),t._modalResizeObserver=new ResizeObserver(()=>{i()}),t._modalResizeObserver.observe(n)),t._modalResizeHandler&&window.removeEventListener("resize",t._modalResizeHandler),t._modalResizeHandler=i,window.addEventListener("resize",t._modalResizeHandler)};window.closeModal=function(e){const t=document.getElementById(e);if(!t)return;const n=t.querySelector(".modal-content");t.classList.remove("opacity-100"),t.classList.add("opacity-0"),n&&(n.classList.remove("scale-100","translate-y-0"),n.classList.add("scale-95","-translate-y-6")),t._modalResizeObserver&&(t._modalResizeObserver.disconnect(),t._modalResizeObserver=null),t._modalResizeHandler&&(window.removeEventListener("resize",t._modalResizeHandler),t._modalResizeHandler=null),setTimeout(()=>{t.classList.remove("flex","items-center","items-start","justify-center"),n==null||n.classList.remove("mt-[5vh]","mt-[10vh]"),t.classList.add("hidden"),document.querySelector(".modal-overlay:not(.hidden)")||(document.documentElement.classList.remove("overflow-hidden"),document.body.classList.remove("overflow-hidden"))},300)};document.addEventListener("click",function(e){e.target.classList.contains("modal-overlay")&&closeModal(e.target.id)});document.addEventListener("keydown",function(e){e.key==="Escape"&&document.querySelectorAll(".modal-overlay").forEach(function(t){t.classList.contains("hidden")||closeModal(t.id)})});window.showToast=function(e,t="success"){let n=t==="error",i=n?"#b91c1c":"#057a37",a=n?'<i class="fa-solid fa-circle-xmark" style="font-size: 16px;"></i>':'<i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>',o=$("#toastContainer");o.length===0&&typeof $<"u"&&$("body").length>0&&($("body").append(`
                <div id="toastContainer" style="position: fixed; bottom: 24px; right: 24px; z-index: 999999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"></div>
            `),o=$("#toastContainer"));let r=`
        <div id="${"toast_"+Date.now()+"_"+Math.random().toString(36).substr(2,4)}" style="display: flex; align-items: center; gap: 12px; background-color: ${i}; color: #ffffff; padding: 12px 20px; border-radius: 6px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); font-size: 13px; font-weight: 600; opacity: 0; transform: translateY(20px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: auto;">
            ${a}
            <span>${e}</span>
        </div>
    `;if(typeof $<"u"&&o.length>0){let l=$(r);o.append(l),setTimeout(()=>{l.css({opacity:"1",transform:"translateY(0)"})},10),setTimeout(()=>{l.css({opacity:"0",transform:"translateY(20px)"}),setTimeout(()=>l.remove(),300)},3500)}};window.formatTanggal=function(e){if(!e)return"-";const t=new Date(e);if(isNaN(t.getTime()))return e;const n=String(t.getDate()).padStart(2,"0"),i=String(t.getMonth()+1).padStart(2,"0"),a=t.getFullYear();return`${n}-${i}-${a}`};window.formatBadgeStatus=function(e){if(!e)return'<span class="inline-block px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-semibold">-</span>';let t=e,n="bg-primary text-white",i=String(e).toLowerCase();return i.includes("ditolak")||i.includes("batal")||i==="reject"?n="bg-red-600 text-white":i==="diterima"||i==="selesai"||i==="approved"?n="bg-emerald-600 text-white":i.includes("wawancara")||i==="proses"||i==="pengecekan"||i==="pemasangan"?n="bg-amber-500 text-white":(i==="ditinjau"||i==="lolos administrasi"||i==="baru"||i==="pending")&&(n="bg-blue-600 text-white"),`<span class="inline-block px-3 py-1 rounded-lg ${n} text-[11px] font-semibold">${t}</span>`};
