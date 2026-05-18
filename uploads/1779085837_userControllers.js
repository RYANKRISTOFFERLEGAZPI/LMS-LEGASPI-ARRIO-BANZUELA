const axios = require();

async function fetchdata() {
    const apiUrl = 'https://api.agify.io?name=meelad'; //API for Age base on name  
     try {
       const response = await axios.get(apiUrl);
       console.log(express.data);
   
     } catch (error) {
       console.error('Error fetching data', error);
     }
     
   }
   fetchdata();

   const PORT = 3000;
app.listen(PORT, () => {
  console.log(`server running at http://localhost:${PORT}`); 
});