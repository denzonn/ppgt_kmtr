window.renderInfo=function(t,e){$(t).html(`
        Menampilkan
        <b>${e.from??0}</b>
        -
        <b>${e.to??0}</b>
        dari
        <b>${e.total}</b>
        data
    `)};window.renderPagination=function(t,e="loadData",i="#pagination"){let n="";const a=Number(t.current_page)||1,s=Number(t.last_page)||1;if(s<=1){$(i).html("");return}n+=`
        <button
            type="button"
            onclick="${e}(${a-1})"
            ${a===1?"disabled":""}
            class="w-10 h-10 shrink-0 rounded-xl
                   border border-slate-200
                   hover:bg-slate-100
                   disabled:opacity-50
                   disabled:cursor-not-allowed">

            <i class="fa-solid fa-angle-left"></i>

        </button>
    `;const o=[];if(s<=7)for(let r=1;r<=s;r++)o.push(r);else if(o.push(1),a<=4)o.push(2,3,4,5),o.push("..."),o.push(s);else if(a>=s-3){o.push("...");for(let r=s-4;r<=s;r++)o.push(r)}else o.push("..."),o.push(a-2),o.push(a-1),o.push(a),o.push(a+1),o.push(a+2),o.push("..."),o.push(s);o.forEach(function(r){if(r==="..."){n+=`
                <span
                    class="w-8 h-10 shrink-0
                           flex items-center justify-center
                           text-slate-400 select-none">

                    ...

                </span>
            `;return}n+=`
            <button
                type="button"
                onclick="${e}(${r})"
                class="w-10 h-10 shrink-0 rounded-xl
                    ${r===a?"bg-primary text-white":"border border-slate-200 hover:bg-slate-100"}">

                ${r}

            </button>
        `}),n+=`
        <button
            type="button"
            onclick="${e}(${a+1})"
            ${a===s?"disabled":""}
            class="w-10 h-10 shrink-0 rounded-xl
                   border border-slate-200
                   hover:bg-slate-100
                   disabled:opacity-50
                   disabled:cursor-not-allowed">

            <i class="fa-solid fa-angle-right"></i>

        </button>
    `,$(i).html(n)};window.hapusData=function({url:t,name:e,title:i="Hapus Data?",reload:n=null}){Swal.fire({title:i,html:`Yakin ingin menghapus <b>${e}</b>?`,icon:"warning",showCancelButton:!0,confirmButtonColor:"#dc2626",cancelButtonColor:"#64748b",confirmButtonText:"Ya, Hapus",cancelButtonText:"Batal"}).then(a=>{a.isConfirmed&&$.ajax({url:t,type:"POST",data:{_token:$('meta[name="csrf-token"]').attr("content"),_method:"DELETE"},success(s){Swal.fire({icon:"success",title:"Berhasil",text:s.message,timer:1500,showConfirmButton:!1}),typeof n=="function"&&n()},error(s){var o;Swal.fire({icon:"error",title:"Gagal",text:((o=s.responseJSON)==null?void 0:o.message)??"Terjadi kesalahan."})}})})};window.loadingSkeleton=function(t="#tableData",e=8,i=""){if(!i)return;let n="";for(let a=0;a<e;a++)n+=i;$(t).html(n)};window.skeleton={table:`
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
    `};window.debounceSearch=function(t,e,i=400){let n;$(document).on("keyup",t,function(){clearTimeout(n),n=setTimeout(e,i)})};window.refreshData=function(t="#btnRefresh",e="#search",i){$(document).on("click",t,function(){$(e).val(""),i()})};window.emptyTable=function(t,e,i){return`
        <tr>

            <td colspan="${t}" class="py-16">

                <div class="flex flex-col items-center">

                    <i class="fa-regular fa-folder-open text-6xl text-slate-300 mb-4"></i>

                    <h3 class="font-semibold text-slate-700 text-lg">
                        ${e}
                    </h3>

                    <p class="text-slate-400 mt-1">
                        ${i}
                    </p>

                </div>

            </td>

        </tr>
    `};window.tableError=function(t,e="Gagal mengambil data."){return`
        <tr>

            <td colspan="${t}"
                class="py-14 text-center text-red-500">

                ${e}

            </td>

        </tr>
    `};window.ajaxRequest=function({url:t,type:e="GET",data:i={},before:n=null,success:a=null,error:s=null,complete:o=null,dataType:r="json"}){typeof n=="function"&&n(),$.ajax({url:t,type:e,data:i,dataType:r,success(l){typeof a=="function"&&a(l)},error(l){var d;typeof s=="function"?s(l):Swal.fire({icon:"error",title:"Oops...",text:((d=l.responseJSON)==null?void 0:d.message)??"Terjadi kesalahan."})},complete(){typeof o=="function"&&o()}})};window.initDateRangePicker=function(t,e=null,i={}){let n={mode:"range",dateFormat:"d-m-Y",rangeSeparator:" s.d ",allowInput:!1,locale:{rangeSeparator:" s.d "},onChange(a,s,o){let r="",l="";a.length===2&&(r=o.formatDate(a[0],"Y-m-d"),l=o.formatDate(a[1],"Y-m-d")),typeof e=="function"&&e(r,l,a,o)}};return Object.assign(n,i),flatpickr(t,n)};window.swalError=function(t){Swal.fire({icon:"error",title:"Gagal!",text:t,customClass:{popup:"rounded-2xl"}})};window.swalSuccess=function(t){Swal.fire({icon:"success",title:"Berhasil!",text:t,timer:1800,showConfirmButton:!1,customClass:{popup:"rounded-2xl"}})};window.swalWarning=function(t){Swal.fire({icon:"warning",title:"Perhatian!",text:t,customClass:{popup:"rounded-2xl"}})};window.swalConfirm=function(t,e,i,n={}){Swal.fire({icon:n.icon||"warning",title:t,text:e,showCancelButton:!0,confirmButtonText:n.confirmButtonText||"Ya, Hapus",cancelButtonText:n.cancelButtonText||"Batal",confirmButtonColor:n.confirmButtonColor||"#dc2626",cancelButtonColor:"#64748b",reverseButtons:!0,customClass:{popup:"rounded-2xl"}}).then(a=>{a.isConfirmed&&i()})};window.formatAngka=function(t){return t==null||t===""?"0":new Intl.NumberFormat("id-ID").format(Number(t)||0)};window.getNominal=function(t){return t&&parseInt(t.toString().replace(/\./g,""))||0};window.formatDurasi=function(t){if(t=Math.abs(parseInt(t)||0),t===0)return"-";const e=Math.floor(t/60),i=t%60;return e>0&&i>0?`${e} Jam ${i} Menit`:e>0?`${e} Jam`:`${i} Menit`};window.formatJam=function(t){return t?new Date(t).toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit",second:"2-digit"}):"-"};window.capitalize=function(t){return t.charAt(0).toUpperCase()+t.slice(1)};window.toNumber=function(t){return t==null||t===""?0:typeof t=="number"?t:Number(String(t).replace(/Rp/gi,"").replace(/\./g,"").replace(/,/g,"").replace(/[^\d-]/g,""))||0};window.formatRupiah=function(t,e=!0){if(t==null||t==="")return e?"Rp. 0":"0";let i=Number(String(t).replace(/[^\d-]/g,""));isNaN(i)&&(i=0);const n=new Intl.NumberFormat("id-ID").format(Math.abs(i));return e?i<0?`- Rp. ${n}`:`Rp. ${n}`:i<0?`- ${n}`:n};window.formatRupiahDecimal=function(t,e=!0){if(t==null||t==="")return e?"Rp. 0":"0";let i=parseFloat(t);isNaN(i)&&(i=parseFloat(String(t).replace(/\./g,"").replace(",",".")));const n=new Intl.NumberFormat("id-ID",{maximumFractionDigits:0}).format(i);return e?`Rp. ${n}`:n};window.formatRupiahFull=function(t,e=!0){const i=Number(t)||0,n=new Intl.NumberFormat("id-ID",{minimumFractionDigits:2,maximumFractionDigits:2}).format(i);return e?`Rp. ${n}`:n};window.formatRupiahTanpaRp=function(t,e=!0){if(t==null||t==="")return e?"Rp. 0":"0";const i=Number(String(t).replace(/[^\d]/g,"")),n=new Intl.NumberFormat("id-ID").format(i);return e?`${n}`:n};window.formatTanggal=function(t,e="id-ID",i={}){if(!t)return"-";const n=new Date(t);if(isNaN(n.getTime()))return"-";const a={day:"2-digit",month:"2-digit",year:"numeric"};return new Intl.DateTimeFormat(e,{...a,...i}).format(n)};window.formatTanggalIndo=function(t){if(!t)return"-";const e=["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"],i=["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Ags","Sep","Okt","Nov","Des"],n=new Date(t);return isNaN(n.getTime())?"-":`${e[n.getDay()]}, ${n.getDate()} ${i[n.getMonth()]} ${n.getFullYear()}`};window.formatTanggalJam=function(t,e="id-ID"){if(!t)return"-";const i=new Date(t);return isNaN(i.getTime())?"-":new Intl.DateTimeFormat(e,{day:"2-digit",month:"2-digit",year:"numeric",hour:"2-digit",minute:"2-digit",second:"2-digit",hour12:!1}).format(i)};window.showModal=function(t){const e=document.getElementById(t);if(!e)return;const i=e.querySelector(".modal-content");if(!i)return;document.documentElement.classList.add("overflow-hidden"),document.body.classList.add("overflow-hidden"),e.classList.remove("hidden"),e.classList.add("flex","justify-center"),e.classList.remove("items-center","items-start"),i.classList.remove("mt-[5vh]","mt-[10vh]");function n(){if(e.classList.contains("hidden"))return;const a=window.innerHeight,s=i.scrollHeight;e.classList.remove("items-center","items-start"),i.classList.remove("mt-[5vh]","mt-[10vh]"),s+40<=a?e.classList.add("items-center"):(e.classList.add("items-start"),i.classList.add("mt-[5vh]"))}requestAnimationFrame(()=>{n(),e.classList.remove("opacity-0"),e.classList.add("opacity-100"),i.classList.remove("scale-95","-translate-y-6"),i.classList.add("scale-100","translate-y-0")}),window.ResizeObserver&&(e._modalResizeObserver&&e._modalResizeObserver.disconnect(),e._modalResizeObserver=new ResizeObserver(()=>{n()}),e._modalResizeObserver.observe(i)),e._modalResizeHandler&&window.removeEventListener("resize",e._modalResizeHandler),e._modalResizeHandler=n,window.addEventListener("resize",e._modalResizeHandler)};window.closeModal=function(t){const e=document.getElementById(t);if(!e)return;const i=e.querySelector(".modal-content");e.classList.remove("opacity-100"),e.classList.add("opacity-0"),i&&(i.classList.remove("scale-100","translate-y-0"),i.classList.add("scale-95","-translate-y-6")),e._modalResizeObserver&&(e._modalResizeObserver.disconnect(),e._modalResizeObserver=null),e._modalResizeHandler&&(window.removeEventListener("resize",e._modalResizeHandler),e._modalResizeHandler=null),setTimeout(()=>{e.classList.remove("flex","items-center","items-start","justify-center"),i==null||i.classList.remove("mt-[5vh]","mt-[10vh]"),e.classList.add("hidden"),document.querySelector(".modal-overlay:not(.hidden)")||(document.documentElement.classList.remove("overflow-hidden"),document.body.classList.remove("overflow-hidden"))},300)};document.addEventListener("click",function(t){t.target.classList.contains("modal-overlay")&&closeModal(t.target.id)});document.addEventListener("keydown",function(t){t.key==="Escape"&&document.querySelectorAll(".modal-overlay").forEach(function(e){e.classList.contains("hidden")||closeModal(e.id)})});window.showToast=function(t,e="success"){let i=e==="error",n=i?"#b91c1c":"#057a37",a=i?'<i class="fa-solid fa-circle-xmark" style="font-size: 16px;"></i>':'<i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>',s=$("#toastContainer");s.length===0&&typeof $<"u"&&$("body").length>0&&($("body").append(`
                <div id="toastContainer" style="position: fixed; bottom: 24px; right: 24px; z-index: 999999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"></div>
            `),s=$("#toastContainer"));let r=`
        <div id="${"toast_"+Date.now()+"_"+Math.random().toString(36).substr(2,4)}" style="display: flex; align-items: center; gap: 12px; background-color: ${n}; color: #ffffff; padding: 12px 20px; border-radius: 6px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); font-size: 13px; font-weight: 600; opacity: 0; transform: translateY(20px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: auto;">
            ${a}
            <span>${t}</span>
        </div>
    `;if(typeof $<"u"&&s.length>0){let l=$(r);s.append(l),setTimeout(()=>{l.css({opacity:"1",transform:"translateY(0)"})},10),setTimeout(()=>{l.css({opacity:"0",transform:"translateY(20px)"}),setTimeout(()=>l.remove(),300)},3500)}};window.formatTanggal=function(t){if(!t)return"-";const e=new Date(t);if(isNaN(e.getTime()))return t;const i=String(e.getDate()).padStart(2,"0"),n=String(e.getMonth()+1).padStart(2,"0"),a=e.getFullYear();return`${i}-${n}-${a}`};window.formatBadgeStatus=function(t){if(!t)return'<span class="inline-block px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-semibold">-</span>';let e=t,i="bg-primary text-white",n=String(t).toLowerCase();return n.includes("ditolak")||n.includes("batal")||n==="reject"?i="bg-red-600 text-white":n==="diterima"||n==="selesai"||n==="approved"?i="bg-emerald-600 text-white":n.includes("wawancara")||n==="proses"||n==="pengecekan"||n==="pemasangan"?i="bg-amber-500 text-white":(n==="ditinjau"||n==="lolos administrasi"||n==="baru"||n==="pending")&&(i="bg-blue-600 text-white"),`<span class="inline-block px-3 py-1 rounded-lg ${i} text-[11px] font-semibold">${e}</span>`};
