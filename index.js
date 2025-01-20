const express = require("express");
const app = express();
const port = 3000;

app.post("/", (req, res) => {
  res.send([
    {
      userprincipalname: "department3-editor@example.com",
    },
  ]);
});

app.listen(port, () => {
  console.log(`Mock server listening on port ${port}`);
});
