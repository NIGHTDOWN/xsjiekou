// app.js
const CryptoJS = require('./cy');
const express = require('express');
const bodyParser = require('body-parser');

// let y = 'U2FsdGVkX1855ZWHDfuWj1Yk/F0zs8AD+CqFuvXrfGpskSTwFXgEPDTSbdZF1JyqO7XVZYboKiAWJ0TOWu6XNw=='

// t =t.toString(CryptoJS.enc.Utf8)
// console.log(t);


const app = express();
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
const port = 3000;
app.get('/decode/', (req, res) => {
    console.log(req.body);
    const { str, key } = req.body;
   // 简单的参数验证
        if (!str || !key) {
            return res.status(400).send('密文和密钥参数不能为空');
        }

        try {
            let decryptedText=getstr(str,key);
            res.send(`解密后的文本是: ${decryptedText}`);  
        } catch (error) {
            res.status(500).send('解密出错');
        }
  });
  function getstr(str,key){
    let t = CryptoJS.AES.decrypt(str, key).t.toString(CryptoJS.enc.Utf8);
    return t;
  }




app.listen(port, () => {
    console.log(`解密服务运行在 http://localhost:${port}`);
  });