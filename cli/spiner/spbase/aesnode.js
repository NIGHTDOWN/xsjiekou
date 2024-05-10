// app.js
const CryptoJS = require('./cy');
const express = require('express');
const bodyParser = require('body-parser');

// let y = 'U2FsdGVkX1855ZWHDfuWj1Yk/F0zs8AD+CqFuvXrfGpskSTwFXgEPDTSbdZF1JyqO7XVZYboKiAWJ0TOWu6XNw=='

// t =t.toString(CryptoJS.enc.Utf8)
// console.log(t);


const app = express();

// app.use(bodyParser.json());
// app.use(bodyParser.urlencoded({ extended: false  }));
app.use(bodyParser.json({ limit: '50mb' }));

// 为 urlencoded 中间件设置更大的大小限制
app.use(bodyParser.urlencoded({ limit: '50mb', extended: true }));
const port = 3000;
function getstr(str,key){
    const  t = CryptoJS.AES.decrypt(str, key).toString(CryptoJS.enc.Utf8);
    // console.log(t); 
    return t;
  }
app.post('/decode/', (req, res) => {
    // console.log(req.body);
    const { str, key } = req.body;
   // 简单的参数验证
        if (!str || !key) {
            return res.status(400).send('密文和密钥参数不能为空');
        }
        try {
            const  decryptedText=    getstr(str,key);
            // console.log(decryptedText);
            res.send(`${decryptedText}`);  
        } catch (error) {
            res.status(500).send('解密出错');
        }
  });
app.listen(port, () => {
    console.log(`解密服务运行在 http://localhost:${port}`);
  });