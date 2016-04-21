/**
 * This file just example of C# code to show how to generate security signature
 * Original WinForm of this file had three text fields: 
 *     timestamp (numeric unix time stamp)
 *     parameters (Ex.: limit=10&suppress_response_code=false&debug=true ) 
 *     sha - generated result of this method
 * Generates SHA256 same as generated in PHP with same incoming values
 *
 */

using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Security.Cryptography;

namespace WindowsFormsApplication1
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }

        private void button1_Click(object sender, EventArgs e)
        {
            string utc = timestamp.Text;
            string paramStr = data.Text;
            string pubKey = "YOUR_PUBLIC_KEY";
            string privKey = "YOUR_PRIVATE_KEY";

            string message = utc + pubKey + paramStr;

            string hashHMACHex = BitConverter.ToString(hmacSHA256(message, privKey)).Replace("-", "").ToLower();

            sha.Text = hashHMACHex;
        }

        static byte[] hmacSHA256(String data, String key)
        {
            using (HMACSHA256 hmac = new HMACSHA256(Encoding.ASCII.GetBytes(key)))
            {
                return hmac.ComputeHash(Encoding.ASCII.GetBytes(data));
            }
        }

    }
}
