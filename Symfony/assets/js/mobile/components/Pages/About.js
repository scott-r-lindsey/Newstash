import React from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";

export default class stub extends React.Component {
  constructor(props, context) {
    super(props, context);
  }

  componentDidMount() {
    console.log('did mount');
  }

  render() {

    return (
      <div>
        <Helmet>
          <title>Books to Love</title>
        </Helmet>
        <strong>All about</strong>
      </div>
    );

  }
}
