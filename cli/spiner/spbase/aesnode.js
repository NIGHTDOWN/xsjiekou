// app.js
const CryptoJS = require('./cy');
const express = require('express');


// let y = 'U2FsdGVkX1855ZWHDfuWj1Yk/F0zs8AD+CqFuvXrfGpskSTwFXgEPDTSbdZF1JyqO7XVZYboKiAWJ0TOWu6XNw=='

// t =t.toString(CryptoJS.enc.Utf8)
// console.log(t);


const app = express();
const port = 3000;
app.get('/decode/', (req, res) => {
    const { str, key } = req.params;
   // 简单的参数验证
        if (!str || !key) {
            return res.status(400).send('密文和密钥参数不能为空');
        }

        // 密钥长度必须为 32 字节（256 位），以十六进制字符串形式
        // if (secretKey.length !== 64) {
        //     return res.status(400).send('密钥必须是 32 字节（64 个十六进制字符）');
        // }
        try {
            let decryptedText=getstr(str,key);
            res.send(`解密后的文本是: ${decryptedText}`);  
        } catch (error) {
            res.status(500).send('解密出错');
        }
    // 假设 IV 是固定的，实际应用中 IV 应该是随机的并且与加密时相同
    // const iv = '000102030405060708090a0b0c0d0e0f';
    
    // aesDecrypt(ciphertext, secretKey, iv)
    //   .then(decryptedText => {
    //     res.send(`解密后的文本是: ${decryptedText}`);
    //   })
    //   .catch(error => {
    //     res.status(500).send('解密出错');
    //   });
  });

  function getstr(str,key){
    let t = CryptoJS.AES.decrypt(str, key).t.toString(CryptoJS.enc.Utf8);
    return t;
  }




app.listen(port, () => {
    console.log(`解密服务运行在 http://localhost:${port}`);
  });