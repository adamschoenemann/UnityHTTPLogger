using UnityEngine;
using Logging;

[RequireComponent(typeof(Rigidbody))]
public class PlayerController : GameObjectLoggable
{

	public float speed = 0.2f;

	public void Update()
	{
		float hor = Input.GetAxis("Horizontal") * Time.deltaTime;
		float ver = Input.GetAxis("Vertical") * Time.deltaTime;

		Vector3 move = new Vector3(hor, 0, ver).normalized;
		transform.Translate(move * speed);

		if(Input.GetKeyDown("space"))
		{
			Jump();
		}
	}

	public void Jump()
	{
		rigidbody.AddForce(Vector3.up * 4.0f, ForceMode.Impulse);
		LogEntry entry = new LogEntry(this, "PlayerJump");
		EnqueueEntry(entry);
	}

}