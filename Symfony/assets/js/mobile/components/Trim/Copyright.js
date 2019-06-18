import React from "react";

function Copyright(props) {
  return (
    <div style={{
        textAlign: 'center',
        padding: '15vw',
        opacity: '.5',
    }}>
      <em> Copyright &copy; { new Date().getFullYear() } Books to Love</em>
    </div>
  );
}

export default Copyright;
