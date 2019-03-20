import React from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";

export default class Work extends React.Component {
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
          <title>This is a work</title>
        </Helmet>
        <strong>Wow so book such SEO</strong>
      </div>
    );

  }
}
