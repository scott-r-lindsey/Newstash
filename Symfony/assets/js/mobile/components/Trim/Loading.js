import React from "react";

class Loading extends React.Component {

  render() {
    return (
      <div className="lds-loading-shell">
        <div className="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
      </div>
    );
  }
}

export default Loading;
