import{T as n,o as l,c as d,w as o,b as s,u as t,Z as u,B as c,t as p,A as w,h as f,m as _,d as b,V as g,a as y,p as V}from"./app-54201836.js";import{_ as h}from"./GuestLayout-f57f2551.js";import"./_plugin-vue_export-helper-c27b6911.js";const k=y("div",{class:"text-subtitle-2 text-medium-emphasis mb-4"}," Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one. ",-1),x={key:0,class:"text-subtitle-2 mb-4"},B=V(" Email Password Reset Link "),E={name:"ForgotPassword",props:{status:{type:String}},setup(a){const e=n({email:""}),i=()=>{e.post("/forgot-password")};return(F,r)=>(l(),d(h,null,{default:o(()=>[s(t(u),{title:"Forgot Password"}),s(g,{onSubmit:b(i,["prevent"])},{default:o(()=>[k,a.status?(l(),c("div",x,p(a.status),1)):w("",!0),s(f,{modelValue:t(e).email,"onUpdate:modelValue":r[0]||(r[0]=m=>t(e).email=m),type:"email",variant:"outlined",density:"compact",placeholder:"Email address","prepend-inner-icon":"mdi-email-outline","error-messages":t(e).errors.email},null,8,["modelValue","error-messages"]),s(_,{loading:t(e).processing,class:"mt-4",type:"submit",block:"",color:"primary"},{default:o(()=>[B]),_:1},8,["loading"])]),_:1},8,["onSubmit"])]),_:1}))}};export{E as default};
